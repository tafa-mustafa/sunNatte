<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\User;
use App\Models\Tontine;
use Illuminate\Support\Str;
use App\Http\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Notifications\DemandeValidatedNotification;

class AdminController extends Controller
{
      public function login_user(Request $request){

        if (!Auth::attempt($request->only('email', 'password'))){
            Helper::sendError('email ou password invalide');

        }
    
            $user = Auth::user();


            

            $success['token'] = $user->createToken('sunuNatte_app')->plainTextToken;
                $success['id'] = $user->id;
                $success['nom'] = $user->nom;
                $success['email'] = $user->email;
         return response()->json($success);
  
    
    }

 public function test(Request $request){

       
         return response()->json('test me');
  
    
    }

    public function list_users()
    {
        $users = User::where('role_id', '!=', '1')->get();

        return response()->json($users);
    }

    public function update_user(Request $request, User $user){
        
         $user->update($request->all());
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
}

    public function delete_user(Request $request, User $user){

        $user->delete();
        return response()->json(['message' => 'Utilisateur supprimé avec succès']);
}
public function show_user(Request $request, User $user){

        return response()->json($user);
 }

 public function active_user($id){

        try {
            $user = User::findOrFail($id);
            $user->statut = true; // ou 1 si vous voulez utiliser le chiffre
            $user->save();

            return response()->json([
                'statut' => true,
                'message' => 'Votre compte a été activé',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'statut' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 500);
        }
 }

    public function desactive_user($id)
    {

        try {
            $user = User::findOrFail($id);
            $user->statut = false; 
            $user->save();

            return response()->json([
                'statut' => true,
                'message' => 'Votre compte a été desactivé',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'statut' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

public function list_tontine()
{
    try {
       $tontines = Tontine::whereHas('users', function ($query) {
    $query->where('adhesions.badge', 'systems');
})
->where('statut', 1)
->get();
        return response()->json($tontines);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Erreur lors de la récupération des tontines',
            'error' => $e->getMessage(),
        ], 500);
    }
}

    
    public function store_tontine(Request $request)
    {
        
        $request->validate([
            'nom' => 'required',
            'nombre_personne' => 'required',
            'type' => '',
            'duree' => '',
            'montant' => '',
            'tirage' => '',
            'code_adhesion' => 'nullable|unique:tontines',
            'materiel_id' => 'nullable',
            'date_demarrage' => 'nullable|date',
            'date_fin' => 'nullable|date',
            'description' => 'nullable',
        ]);

        try {
            DB::beginTransaction();

            $codeAdhesion = Str::random(10);

            // Validation de la date de démarrage
            $dateDemarrage = Carbon::parse($request->date_demarrage);
            $dateFin = $dateDemarrage->copy()->addMonths($request->duree);

            // Formatage des dates
            $dateDemarrageFormat = $dateDemarrage->format('d/m/y');
            $dateFinFormat = $dateFin->format('d/m/y');

            $tontine = Tontine::create([
                'nom' => $request->nom,
                'nombre_personne' => $request->nombre_personne,
                'type' => $request->type,
                'duree' => $request->duree,
                'montant' => $request->montant,
                'tirage' => $request->tirage,
                'code_adhesion' => $codeAdhesion,
                'materiel_id' => $request->materiel_id,
                'date_demarrage' => $dateDemarrageFormat,
                'date_fin' => $dateFinFormat,
                'description' => $request->description,
            ]);

            $createur = Auth::user();

            $tontine->users()->attach($createur->id);

            $createur->adhesions()->update(['badge' => 'systems']);

            DB::commit();

            return response()->json($tontine);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'Erreur lors de la création de la tontine : ' . $e->getMessage()], 500);
        }
    }


    public function show_tontine(Tontine $tontine)
    {
        $members = $tontine->users()->withPivot('badge')->get();
        $data = [
            'id' => $tontine->id,
            'nom' => $tontine->nom,
            'code_adhesion' => $tontine->code_adhesion,
            'nombre_personne' => $tontine->nombre_personne,
            'nombre_membres_actuels' => $tontine->users->count() - 1,
            'nombre_restant' => max(0, $tontine->nombre_personne - $tontine->users->count() + 1),
            'duree' => $tontine->duree,
            'montant' => $tontine->montant,
            'type' => $tontine->type,
            'materiel_image' => $tontine->materiel ? $tontine->materiel->image : null,
            'materiel_titre' => $tontine->materiel ? $tontine->materiel->nom : null,
            'materiel_id' => $tontine->materiel ? $tontine->materiel->id : null,
            'date_demarrage' => $tontine->date_demarrage,
            'date_fin' => $tontine->date_fin,
            'description' => $tontine->description,
            'tirage' => $tontine->tirage,

            'montant_mensuel' => round($tontine->montant / $tontine->nombre_personne, 2),
            'statut' => $tontine->statut,

            'membres' => $members->map(function ($user) {
                return [
                    'id' => $user->id,
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'phone' => $user->phone,
                    'badge' => $user->pivot->badge, // Utilisez pivot pour accéder à la valeur de la colonne "badge"
                ];
            }),
        ];

        return response()->json($data);
    }

    public function active_tontine(Tontine $tontine)
    {
        try {
            $tontine->update(['statut' => true]);
            return response()->json(['message' => 'Tontine activée avec succès']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de l\'activation de la tontine', 'error' => $e->getMessage()], 500);
        }
    }

    public function desactive_tontine(Tontine $tontine)
    {
        try {
            $tontine->update(['statut' => false]);
            return response()->json(['message' => 'Tontine desactivée avec succès']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de l\'activation de la tontine', 'error' => $e->getMessage()], 500);
        }
    }


    public function update_tontine(Request $request, Tontine $tontine)
    {
        $request->validate([
            'nom' => '',
            'nombre_personne' => '',
            'type' => '',
            'duree' => '',
            'montant' => '',
            'tirage' => '',
            'code_adhesion' => 'nullable|unique:tontines,code_adhesion,' . $tontine->id,
            'materiel_id' => 'nullable',
            'date_demarrage' => 'nullable|date',
            'date_fin' => 'nullable|date',
            'description' => 'nullable',
        ]);

        try {
            $dateDemarrage = Carbon::parse($request->date_demarrage);
            $dateFin = $dateDemarrage->copy()->addMonths($request->duree);

            // Formatage des dates
            $dateDemarrageFormat = $dateDemarrage->format('d/m/y');
            $dateFinFormat = $dateFin->format('d/m/y');

            $tontine->update([
                'nom' => $request->nom,
                'nombre_personne' => $request->nombre_personne,
                'type' => $request->type,
                'duree' => $request->duree,
                'montant' => $request->montant,
                'tirage' => $request->tirage,
                'materiel_id' => $request->materiel_id,
                'date_demarrage' => $dateDemarrageFormat,
                'date_fin' => $dateFinFormat,
                'description' => $request->description,
            ]);

            return response()->json(['message' => 'Tontine mise à jour avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la mise à jour de la tontine : ' . $e->getMessage()], 500);
        }
    }

    public function list_documents(User $user)
    {
        try {

            $documents = $user->documents()->get();

            return response()->json($documents);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la récupération des documents de l\'utilisateur', 'error' => $e->getMessage()], 500);
        }
    }


    public function adherer_user(Request $request, Tontine $tontine)
    {
        if ($tontine->users->count() >= $tontine->nombre_personne) {
            return response()->json(['message' => 'Le nombre maximum de participants est atteint.'], 422);
        }

        $userId = $request->input('user_id');
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé.'], 404);
        }
        $badgeActuel = $user->adhesions()->where('tontine_id', $tontine->id)->value('badge');

        if (!$tontine->users->contains($user)) {
            $tontine->users()->attach($user);
            $user->adhesions()->update(['badge' => 'membre']);

            $user->notify(new DemandeValidatedNotification($tontine, []));
            return response()->json(['message' => 'Adhesion faite, une notification a été envoyée.'], 200);
        }

        return response()->json(['message' => 'Vous êtes déjà membre de cette tontine.'], 422);
    }
    

}