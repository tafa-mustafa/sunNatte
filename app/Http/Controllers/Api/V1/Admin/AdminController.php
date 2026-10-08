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
use App\Services\AdminAuthService;
use App\Services\AdminDashboardService;
use App\Services\ProfileCompletenessService;
use App\Models\Mutuelle;

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

        $user->load('documents');

        return response()->json([
            'user' => $user,
            'completeness' => app(ProfileCompletenessService::class)
                ->inspect($user, $user->documents->count()),
        ]);
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
            $tontines = Tontine::withCount([
                'adhesions as pending_count' => fn ($q) => $q->where('statut', 'pending'),
                'adhesions as approved_count' => fn ($q) => $q->where('statut', 'approved'),
            ])->latest()->get();

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
                'nom', 'nombre_personne', 'type', 'visibilite', 'duree',
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



    public function test()
    {
        return response()->json(['ok' => true, 'service' => 'admin']);
    }

    public function show_tontine(Tontine $tontine)
    {
        $this->checkIsAdmin();

        $tontine->load(['users' => function ($q) {
            $q->withPivot('badge', 'statut');
        }]);

        return response()->json($tontine);
    }

    public function active_tontine(Tontine $tontine)
    {
        return $this->toggle_tontine_status($tontine, 1);
    }

    public function desactive_tontine(Tontine $tontine)
    {
        return $this->toggle_tontine_status($tontine, 0);
    }

    public function list_documents(User $user)
    {
        $this->checkIsAdmin();

        return response()->json($user->documents()->latest()->get());
    }

    public function dashboard(AdminDashboardService $dashboard)
    {
        $this->checkIsAdmin();

        return response()->json($dashboard->stats());
    }

    public function list_mutuelles()
    {
        $this->checkIsAdmin();

        return response()->json(
            Mutuelle::with('createur:id,nom,prenom,phone')->latest()->get()
        );
    }

    public function login_user(Request $request, AdminAuthService $auth)
    {
        $request->validate([
            'password' => 'required|string',
            'login' => 'required_without_all:email,phone|nullable|string',
            'email' => 'required_without_all:login,phone|nullable|string',
            'phone' => 'required_without_all:login,email|nullable|string',
        ]);

        $credentials = $auth->credentials($request);
        if (! Auth::attempt($credentials)) {
            Helper::sendError('Identifiants invalides.');
        }

        $user = Auth::user();
        if ((int) $user->role_id !== User::ROLE_ADMIN) {
            Auth::logout();
            Helper::sendError('Accès réservé aux administrateurs.', [], 403);
        }

        return response()->json([
            'token' => $user->createToken('sunuNatte_admin')->plainTextToken,
            'id' => $user->id,
            'nom' => $user->nom,
            'prenom' => $user->prenom,
            'email' => $user->email,
            'phone' => $user->phone,
            'role_id' => $user->role_id,
        ]);
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

    
    

