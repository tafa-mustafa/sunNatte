<?php

namespace App\Http\Controllers\Api\V1\Admin;


use App\Models\User;
use App\Models\Tontine;
use App\Models\Adhesion;
use Illuminate\Support\Str;
use App\Http\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Rules\MatchOldPassword;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\V1\AdhesionController;
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

        $users = User::where('role_id', '!=', 1)->get();
        return response()->json($users);
    }

    /**
     * 🔎 Voir un utilisateur
     */
    public function show_user(User $user)
    {
        $this->checkIsAdmin();

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
            $tontines = Tontine::whereHas('users', function ($query) {
                $query->where('adhesions.badge', 'systems');
            })->where('statut', 1)->get();

            return response()->json($tontines);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la récupération des tontines', 'error' => $e->getMessage()], 500);
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
            'visibilite' => 'nullable|in:catalogue,privee',
            'duree' => 'nullable|integer',
            'montant' => 'nullable|numeric',
            'tirage' => 'nullable|integer',
            'materiel_id' => 'nullable|exists:materiels,id',
            'date_demarrage' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $validated['code_adhesion'] = app(\App\Services\TontineInviteService::class)->generateUnique();
            $validated['visibilite'] = $validated['visibilite'] ?? \App\Models\Tontine::VISIBILITE_CATALOGUE;

            if (isset($validated['date_demarrage']) && $validated['duree']) {
                $dateDemarrage = Carbon::parse($validated['date_demarrage']);
                $validated['date_fin'] = $dateDemarrage->copy()->addMonths($validated['duree']);
            }

            $tontine = Tontine::create($validated);

            DB::commit();

            return response()->json(['message' => 'Tontine créée avec succès', 'tontine' => $tontine], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur lors de la création', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * ✏️ Modifier une tontine
     */
    public function update_tontine(Request $request, Tontine $tontine)
    {
        $this->checkIsAdmin();

        try {
            $tontine->update($request->only([
                'nom', 'nombre_personne', 'type', 'duree',
                'montant', 'tirage', 'materiel_id', 'description'
            ]));

            return response()->json(['message' => 'Tontine mise à jour avec succès']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la mise à jour', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * ✅ Activer/Désactiver une tontine
     */
    public function toggle_tontine_status(Tontine $tontine, $status)
    {
        $this->checkIsAdmin();

        $tontine->statut = $status;
        $tontine->save();

        $msg = $status ? 'activée' : 'désactivée';
        return response()->json(['message' => "Tontine $msg avec succès"]);
    }



    public function login_user(Request $request)
    {

        if (!Auth::attempt($request->only('email', 'password'))) {
            Helper::sendError('email ou password invalide');

        }

        $user = Auth::user();




        $success['token'] = $user->createToken('sunuNatte_app')->plainTextToken;
        $success['id'] = $user->id;
        $success['nom'] = $user->nom;
        $success['email'] = $user->email;
        return response()->json($success);


    }

    public function adherer_user(Request $request, Tontine $tontine)
    {
        $this->checkIsAdmin();

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $adhesion = Adhesion::where('tontine_id', $tontine->id)
            ->where('user_id', $request->user_id)
            ->first();

        if (! $adhesion) {
            return response()->json(['message' => 'Aucune demande d\'adhésion trouvée.'], 404);
        }

        $request->merge(['action' => 'approve']);

        return app(AdhesionController::class)->review($request, $adhesion);
    }

}

    
    

