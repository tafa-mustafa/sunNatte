<?php

namespace App\Http\Controllers\Api\V1\Admin;


use App\Models\Document;
use App\Models\User;
use App\Models\Tontine;
use Illuminate\Support\Str;
use App\Http\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Rules\MatchOldPassword;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Notifications\DemandeValidatedNotification;

class AdminController extends Controller
{
    /**
     * 🔒 Vérifie si l'utilisateur est admin
     */
    private function checkIsAdmin()
    {
        $user = Auth::user();
        if ($user->role_id !== 1) {
            abort(403, 'Vous n\'avez pas l\'autorisation.');
        }
    }

    /**
     * ✅ Créer un utilisateur
     */
    public function store(Request $request)
    {
        $this->checkIsAdmin();

        $validated = $request->validate([
            'prenom' => 'required|string|max:255',
            'nom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'phone2' => 'nullable|string|unique:users,phone',
            'password' => 'required|string|min:6|confirmed',
            'role_id' => 'required|integer',
            'adresse' => 'nullable|string|max:255',
            'profession' => 'nullable|string|max:255',
            'num_cni' => 'nullable|string|max:255',
            'num_passport' => 'nullable|string|max:255',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        return response()->json(['message' => 'Utilisateur créé avec succès.', 'user' => $user], 201);
    }

    /**
     * 📝 Lister tous les utilisateurs sauf admin
     */
    public function list_users()
    {
        $this->checkIsAdmin();

        $users = User::where('role_id', '!=', 1)
            ->with([
                'documents' => function ($query) {
                    $query->select( 'nom','image', 'statut');
                }
            ])
            ->get();
        return response()->json($users);
    }

    /**
     * 🔎 Voir un utilisateur
     */
    public function show_user(User $user)
    {
        $this->checkIsAdmin();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User introuvable.'
            ], 404);
        }
        return response()->json($user);
    }

    /**
     * ✏️ Modifier un utilisateur
     */
    public function update_user(Request $request, User $user)
    {
        $this->checkIsAdmin();

        try {
            $user->update($request->only([
                'prenom', 'nom', 'email', 'phone', 'phone2',
                'adresse', 'profession', 'num_cni', 'num_passport'
            ]));

            if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
                if ($user->avatar) {
                    Storage::delete($user->avatar);
                }

                $file_name = time() . '.' . $request->avatar->extension();
                $path = $request->avatar->storeAs('avatars', $file_name);
                $user->avatar = $path;
                $user->save();
            }

            return response()->json(['message' => 'Utilisateur mis à jour avec succès']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🗑️ Supprimer un utilisateur
     */
    public function delete_user(User $user)
    {
        $this->checkIsAdmin();

        $user->delete();
        return response()->json(['message' => 'Utilisateur supprimé avec succès']);
    }

    /**
     * ✅ Activer un utilisateur
     */
    public function active_user($id)
    {
        $this->checkIsAdmin();

        try {
            $user = User::findOrFail($id);
            $user->statut = true;
            $user->save();

            return response()->json(['message' => 'Utilisateur activé avec succès']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * ❌ Désactiver un utilisateur
     */
    public function desactive_user($id)
    {
        $this->checkIsAdmin();

        try {
            $user = User::findOrFail($id);
            $user->statut = false;
            $user->save();

            return response()->json(['message' => 'Utilisateur désactivé avec succès']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * 📝 Liste des tontines actives
     */
    public function list_tontine()
{
    $this->checkIsAdmin();

    try {
        // Récupère toutes les tontines avec leurs membres
        // en excluant ceux qui ont role_id = 1 ou 2
        $tontines = Tontine::with(['users' => function ($query) {
            $query->select('users.id', 'users.nom', 'users.prenom', 'users.email', 'users.role_id')
                  ->whereNotIn('users.role_id', [1, 2]); // ⬅️ Exclure les rôles admin
        }])->paginate(10);

        return response()->json([
            'message' => 'Liste des tontines récupérée avec succès.',
            'data' => $tontines
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Erreur lors de la récupération des tontines.',
            'error' => $e->getMessage()
        ], 500);
    }
}


    /**
     * ➕ Créer une tontine
     */
    public function store_tontine(Request $request)
{
    $this->checkIsAdmin();

    $validated = $request->validate([
        'nom' => 'required|string|max:255',
        'nombre_personne' => 'required|integer',
        'type' => 'nullable|string',
        'duree' => 'nullable|integer',
        'montant' => 'nullable|numeric',
        'tirage' => 'nullable|string',
        'statut_tirage' => 'nullable|string',
        'materiel_id' => 'nullable|exists:materiels,id',
        'date_demarrage' => 'nullable|date',
        'description' => 'nullable|string',
    ]);

    try {
        DB::beginTransaction();

        // Génération d’un code aléatoire pour l’adhésion
        $validated['code_adhesion'] = Str::random(10);

        // Calcul de la date de fin si date_demarrage et durée sont fournis
        if (!empty($validated['date_demarrage']) && !empty($validated['duree'])) {
            $dateDemarrage = \Carbon\Carbon::parse($validated['date_demarrage']);
            $validated['date_demarrage'] = $dateDemarrage->format('Y-m-d');
            $validated['date_fin'] = $dateDemarrage->copy()->addMonths($validated['duree']);
        }

        // Création de la tontine
        $tontine = Tontine::create($validated);

        // Ajout du créateur comme membre
        $createur = Auth::user();
        $tontine->users()->attach($createur->id);

        // Mise à jour du badge du créateur
        $createur->adhesions()->update(['badge' => 'systems']); // Corrigé ici

        DB::commit();

        return response()->json([
            'message' => 'Tontine créée avec succès',
            'tontine' => $tontine
        ], 201);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Erreur lors de la création',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * ✏️ Modifier une tontine
     */
    public function update_tontine(Request $request, $id)
    {
        $this->checkIsAdmin();
        if (!$id) {
            return response()->json([
                'status' => false,
                'message' => 'Tontine introuvable.'
            ], 404);
        }
    $validated = $request->validate([
        'nom' => 'sometimes|required|string|max:255',
        'nombre_personne' => 'sometimes|required|integer',
        'type' => 'nullable|string',
        'duree' => 'nullable|integer',
        'montant' => 'nullable|numeric',
        'tirage' => 'nullable|string',
        'statut_tirage' => 'nullable|string',
        'materiel_id' => 'nullable|exists:materiels,id',
        'date_demarrage' => 'nullable|date',
        'description' => 'nullable|string',
    ]);

    try {
        DB::beginTransaction();

        $tontine = Tontine::findOrFail($id);

        // Si la date de démarrage et la durée sont fournies, recalculer la date de fin
        if (!empty($validated['date_demarrage']) && !empty($validated['duree'])) {
            $dateDemarrage = \Carbon\Carbon::parse($validated['date_demarrage']);
            $validated['date_demarrage'] = $dateDemarrage->format('Y-m-d');
            $validated['date_fin'] = $dateDemarrage->copy()->addMonths($validated['duree']);
        } elseif (!empty($validated['duree']) && $tontine->date_demarrage) {
            // Si seule la durée est modifiée
            $dateDemarrage = \Carbon\Carbon::parse($tontine->date_demarrage);
            $validated['date_fin'] = $dateDemarrage->copy()->addMonths($validated['duree']);
        }

        $tontine->update($validated);

        DB::commit();

        return response()->json([
            'message' => 'Tontine mise à jour avec succès',
            'tontine' => $tontine
        ], 200);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Erreur lors de la mise à jour',
            'error' => $e->getMessage()
        ], 500);
    }
    }

    /**
     * ✅ Activer/Désactiver une tontine
     */
    public function active_tontine_status(Tontine $tontine, $status)
    {
        $this->checkIsAdmin();

        $tontine->statut = $status;
        $tontine->save();

        $msg = $status ? 'activée' : 'désactivée';
        return response()->json(['message' => "Tontine $msg avec succès"]);
    }



    public function login_user(Request $request)
{
    // Valider les champs requis
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|string|min:6',
    ]);

    // Vérifier l'authentification
    if (!Auth::attempt($request->only('email', 'password'))) {
        return response()->json([
            'error' => 'Email ou mot de passe invalide.'
        ], 401);
    }

    $user = Auth::user();

    // Vérifier si le user a le role_id = 1
    if ($user->role_id !== 1) {
        Auth::logout(); // Déconnecter immédiatement
        return response()->json([
            'error' => 'Vous n\'êtes pas autorisé à vous connecter à cette application.'
        ], 403);
    }

    // Créer le token
    $success['token'] = $user->createToken('sunuNatte_app')->plainTextToken;
    $success['id'] = $user->id;
    $success['nom'] = $user->nom;
    $success['email'] = $user->email;

    return response()->json($success);
}








public function statat()
{
    try {
        // Nombre total d'utilisateurs
        $nbUsers = User::count();

        // Nombre d'admins
        $nbAdminUsers = User::where('role_id', 1)->count();


         $nbsimpleUsers = User::where('role_id', 3)->count();
        // Somme des montants pour les tontines actives créées par un admin
        //where('statut', 1)
        $montants = Tontine::whereHas('users', function ($query) {
                $query->where('role_id', [1,2 ,3]);
            })
            ->sum('montant');

        // Nombre de tontines créées par des admins
        $tontines_admin = Tontine::whereHas('users', function ($query) {
            $query->where('role_id', 1);
        })->count();

        // Nombre de tontines créées par des utilisateurs
        $tontines_user = Tontine::whereHas('users', function ($query) {
            $query->where('role_id', 3);
        })->count();

        return response()->json([
            'tontines_user' => $tontines_user,
            'tontines_admin' => $tontines_admin,
            'cagnotte' => $montants,
            'nb_users' => $nbsimpleUsers,
            'nb_admin_users' => $nbAdminUsers,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Erreur lors du comptage des utilisateurs.',
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * ✅ Valider un document utilisateur
 */

    /**
     * ✅ Activer/Désactiver une tontine
     */
    public function active_document_status(Document $document, $status)
    {
        $this->checkIsAdmin();


        if (!$document) {
            return response()->json([
                'status' => false,
                'message' => 'Document introuvable.'
            ], 404);
        }


        $document->statut = true;
        $document->save();
        $msg = $status ? 'activée' : 'désactivée';
        return response()->json(['message' => "Tontine $msg avec succès"]);
    }



  }





