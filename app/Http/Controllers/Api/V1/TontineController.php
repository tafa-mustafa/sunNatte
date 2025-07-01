<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Notifications\TontineNotification;
use App\Models\{Tontine, User, Adhesion, Materiel};
use Illuminate\Support\Str;
use App\Models\Tirage;
class TontineController extends Controller
{

    public function list_avenir()
    {
        try {

            $utilisateur = User::findOrFail(1);

            $tontine = $utilisateur->tontines;

            $tontinesFiltrees = $tontine->filter(function ($tontine) {
                return $tontine->users->count() <= $tontine->nombre_personne;
            });
            $tontinesDetails = [];

            foreach ($tontinesFiltrees as $tontine) {
                $tontineDetails = [
                    'id' => $tontine->id,
                    'nom' => $tontine->nom,
                    'nombre_personne' => $tontine->nombre_personne,
                    'nombre_membres_actuels' => $tontine->users->count() - 1,
                    'nombre_restant' => max(0, $tontine->nombre_personne - $tontine->users->count() - 1),
                    'type' => $tontine->type,
                    'code_adhesion' => $tontine->code_adhesion,              
                    'duree' => $tontine->duree,
                    'montan' => $tontine->montant,
                    'materiel_image' => $tontine->materiel ? $tontine->materiel->image : null,
                    'materiel_titre' => $tontine->materiel ? $tontine->materiel->nom : null,
                    'materiel_id' => $tontine->materiel ? $tontine->materiel->id : null,
                    'date_demarrage' => $tontine->date_demarrage,
                    'date_fin' => $tontine->date_fin,
                    'description' => $tontine->description,
                    'tirage' => $tontine->tirage,

                ];
                $tontinesDetails[] = $tontineDetails;
            }

            return response()->json($tontinesDetails, 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la récupération des tontines', 'error' => $e->getMessage()], 500);
        }
    }


    public function tontinesExpired()
    {
        try {
            $tontines = Tontine::where('date_fin', '<', Carbon::now())->get();
            return response()->json( $tontines);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la récupération des tontines', 'error' => $e->getMessage()], 500);
        }
    }
    
 
    public function index()
{
    $user = auth()->user();

    // Charger uniquement les tontines où l'utilisateur est membre
    $tontines = $user->tontines()->with(['users' => function ($query) {
        $query->withPivot('badge');
    }])->get();

    // Transformer les tontines pour inclure les informations nécessaires
    $data = $tontines->map(function ($tontine) {
        return [
            'id' => $tontine->id,
            'nom' => $tontine->nom,
            'code_adhesion' => $tontine->code_adhesion,
            'nombre_personne' => $tontine->nombre_personne,
            'nombre_membres_actuels' => $tontine->users->count(),
            'nombre_restant' => max(0, $tontine->nombre_personne - $tontine->users->count()),
            'duree' => $tontine->duree,
            'montant' => $tontine->montant,
            'type' => $tontine->type,
            'tirage' => $tontine->tirage,

            'date_demarrage' => $tontine->date_demarrage,
            'date_fin' => $tontine->date_fin,
            'description' => $tontine->description,
            'montant_mensuel' => round($tontine->montant / $tontine->nombre_personne, 2),
            'statut' => $tontine->statut,

            // Mapper les membres pour inclure leurs informations et badges
            'membres' => $tontine->users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'phone' => $user->phone,
                    'badge' => $user->pivot->badge,
                ];
            }),
        ];
    });



    // Retourner la liste des tontines avec leurs informations
    return response()->json($data);
}



   public function store(Request $request)
{
    $request->validate([
        'nom' => 'required',
        'nombre_personne' => 'required',
        'type' => 'required',
        'duree' => 'required|integer|min:1',
        'montant' => 'required|numeric|min:1',
        'tirage' => 'required',
        'code_adhesion' => 'nullable|unique:tontines',
        'materiel_id' => 'nullable|exists:materiels,id',
        'date_demarrage' => 'nullable|date|after_or_equal:today',
        'date_fin' => 'nullable|date|after:date_demarrage',
        'description' => 'nullable',
    ]);

    try {
        DB::beginTransaction();

        // Vérification et formatage des dates
        $dateDemarrage = Carbon::parse($request->date_demarrage);
        $dateFin = $request->date_fin 
            ? Carbon::parse($request->date_fin) 
            : $dateDemarrage->copy()->addMonths($request->duree);

        if ($dateFin <= $dateDemarrage) {
            return response()->json(['message' => 'La date de fin doit être postérieure à la date de début.'], 422);
        }

        $tontine = Tontine::create([
            'nom' => $request->nom,
            'nombre_personne' => $request->nombre_personne,
            'type' => $request->type,
            'duree' => $request->duree,
            'montant' => $request->montant,
            'tirage' => $request->tirage,
            'code_adhesion' => Str::random(10),
            'materiel_id' => $request->materiel_id,
            'date_demarrage' => $dateDemarrage->format('Y-m-d'),
            'date_fin' => $dateFin->format('Y-m-d'),
            'description' => $request->description,
        ]);

        $createur = Auth::user();
        $tontine->users()->attach($createur->id);
        $createur->adhesions()->update(['badge' => 'proprio']);

        DB::commit();

        return response()->json(['message' => 'Tontine créée avec succès!'], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => 'Erreur lors de la création de la tontine', 'error' => $e->getMessage()], 500);
    }
}





    public function adhesion(Request $request, Tontine $tontine)
    {

       // $createur = $tontine->users()->withPivot('badge' = 'systems')->get();
        if ($tontine->users->count() >= $tontine->nombre_personne) {
            return response()->json(['message' => 'Le nombre maximum de participants est atteint.'], 422);
        }
       
        $codeAdhesion = $request->input('code_adhesion');
        
        if ($tontine->code_adhesion !== $codeAdhesion) {
            return response()->json(['message' => 'Code d\'adhésion incorrect.'], 422);
        }
        $createur = $tontine->users()->first(); // Supposons que le premier utilisateur de la tontine est le créateur
        $createur->notify(new TontineNotification($tontine, [])); // Passez un tableau vide si aucune donnée supplémentaire n'est nécessaire      
          $users = auth()->user();
        $badgeActuel = $users->adhesions()->where('tontine_id', $tontine->id)->value('badge');

        if (!$tontine->users->contains($users)) {

            $tontine->users()->attach($users);
            $users->adhesions()->update(['badge' => 'membre']);

            return response()->json(['message' => 'Adhesion Fait'], 200);
        }

        return response()->json(['message' => 'Vous êtes déjà membre de cette tontine.'], 422);


    }
    public function show(Tontine $tontine)
    {
        $members = $tontine->users()->withPivot('badge')->get();
        $data = [
            'id' => $tontine->id,
            'nom' => $tontine->nom,
            'code_adhesion' => $tontine->code_adhesion,
            'nombre_personne' => $tontine->nombre_personne,
            'nombre_membres_actuels' => $tontine->users->count(),
            'nombre_restant' => max(0, $tontine->nombre_personne - $tontine->users->count()),
            'duree' => $tontine->duree,
            'tirage' => $tontine->tirage,

            'montant' => $tontine->montant,
            'type' => $tontine->type,
            'materiel_image' => $tontine->materiel ? $tontine->materiel->image : null,
            'materiel_titre' => $tontine->materiel ? $tontine->materiel->nom : null,
            'materiel_id' => $tontine->materiel ? $tontine->materiel->id : null,
            'date_demarrage' => $tontine->date_demarrage,
            'date_fin' => $tontine->date_fin,
            'description' => $tontine->description,

            'montant_mensuel' => round($tontine->montant / $tontine->nombre_personne, 2),
            'statut' => $tontine->statut,

            'membres' => $members->map(function ($user) {
                return [
                    'id' => $user->id,
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'phone' => $user->phone,
                    'badge' => $user->pivot->badge,
                ];
            }),
        ];
        return response()->json($data);
    }


    public function show_mt(Materiel $materiel)
    {

        $data = $materiel->toArray();

        return response()->json($data);
    }




public function tirage(Tontine $tontine)
{
    // Vérifier si un tirage a déjà été effectué
    if ($tontine->tirages()->exists()) {
        return response()->json(['message' => 'Le tirage a déjà été effectué pour cette tontine.'], 422);
    }

    // Vérification : seul le créateur peut effectuer le tirage
    $createur = $tontine->users()
        ->wherePivot('badge', 'proprio')
        ->first();

    if (!$createur || $createur->id !== auth()->id()) {
        return response()->json(['message' => 'Seul le créateur de la tontine peut effectuer le tirage.'], 403);
    }

    // Vérifier si tous les membres sont présents
    $utilisateurs = $tontine->users;
    if ($utilisateurs->count() < $tontine->nombre_personne) {
        return response()->json(['message' => 'Le tirage ne peut pas avoir lieu car les membres ne sont pas au complet.']);
    }

    // Effectuer le tirage
    $utilisateurs = $utilisateurs->toArray();
    shuffle($utilisateurs);

    // Générer les dates de versement
    $dateDemarrage = Carbon::parse($tontine->date_demarrage);
    $tirages = [];
    foreach ($utilisateurs as $index => $user) {
        $dateVersement = $dateDemarrage->copy()->addMonths($index)->day(10);
        $tirages[] = [
            'tontine_id' => $tontine->id,
            'user_id' => $user['id'],
            'date_versement' => $dateVersement->format('Y-m-d'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    // Enregistrer les tirages dans la base de données
    DB::table('tirages')->insert($tirages);

    return response()->json(['message' => 'Tirage effectué avec succès!', 'tirages' => $tirages]);
}



public function listeVersements(Tontine $tontine)
{
    $tirages = $tontine->tirages()
        ->with('user:id,nom,prenom')
        ->orderBy('date_versement')
        ->get();

    return response()->json($tirages);
}





    public function update(Request $request, string $id)
    {
        //
    }


    public function destroy(string $id)
    {
        //
    }

    public function adhesion_tontine(Request $request)
    {
        $codeAdhesion = $request->input('code_adhesion');

        $tontine = Tontine::where('code_adhesion', $codeAdhesion)->first();

        if (!$tontine) {
            return response()->json(['message' => 'Aucune tontine correspondant à ce code d\'adhésion.'], 404);
        }

        if ($tontine->users->count() >= $tontine->nombre_personne) {
            return response()->json(['message' => 'Le nombre maximum de participants est atteint pour cette tontine.'], 422);
        }

        $user = auth()->user();
        if (!$tontine->users->contains($user)) {
            $tontine->users()->attach($user);
            $user->adhesions()->update(['badge' => 'membre']);

            // Notifier le créateur de la tontine s'il existe
            if ($createur = $tontine->users()->first()) {
                $createur->notify(new TontineNotification($tontine, []));
            }

            return response()->json(['message' => 'Adhesion faite, une notification a été envoyée.'], 200);
        }

        return response()->json(['message' => 'Vous êtes déjà membre de cette tontine.'], 422);
    }
    
    

   public function filterByType(Request $request)
{
    // Validation des entrées
    $request->validate([
        'type' => 'nullable|string', // Le type est optionnel
        'nom'  => 'nullable|string',  // Le nom est optionnel
    ]);

    $type = $request->input('type');
    $nom  = $request->input('nom');

    // Construire la requête dynamique
    $query = Tontine::query();

    if ($type) {
        $query->where('type', $type);
    }
    if ($nom) {
        $query->where('nom', 'like', '%' . $nom . '%');
    }

    // Charger les tontines filtrées avec leurs membres (et le champ pivot "badge")
    $tontines = $query->with(['users' => function($q) {
        $q->withPivot('badge');
    }])->get();

    // Si aucune tontine n'est trouvée, retourner un message approprié
    if ($tontines->isEmpty()) {
        return response()->json(['message' => 'Aucune tontine trouvée pour les critères donnés.'], 404);
    }

    // Transformer chaque tontine pour inclure les informations désirées
    $data = $tontines->map(function ($tontine) {
        // Récupérer les membres déjà chargés
        $members = $tontine->users;
        return [
            'id'                      => $tontine->id,
            'nom'                     => $tontine->nom,
            'code_adhesion'           => $tontine->code_adhesion,
            'nombre_personne'         => $tontine->nombre_personne,
            'nombre_membres_actuels'  => $tontine->users->count(),
            'nombre_restant'          => max(0, $tontine->nombre_personne - $tontine->users->count()),
            'duree'                   => $tontine->duree,
            'tirage'                  => $tontine->tirage,
            'montant'                 => $tontine->montant,
            'type'                    => $tontine->type,
            'materiel_image'          => $tontine->materiel ? $tontine->materiel->image : null,
            'materiel_titre'          => $tontine->materiel ? $tontine->materiel->nom : null,
            'materiel_id'             => $tontine->materiel ? $tontine->materiel->id : null,
            'date_demarrage'          => $tontine->date_demarrage,
            'date_fin'                => $tontine->date_fin,
            'description'             => $tontine->description,
            'montant_mensuel'         => round($tontine->montant / $tontine->nombre_personne, 2),
            'statut'                  => $tontine->statut,
            'membres'                 => $members->map(function ($user) {
                return [
                    'id'      => $user->id,
                    'nom'     => $user->nom,
                    'prenom'  => $user->prenom,
                    'phone'   => $user->phone,
                    'badge'   => $user->pivot->badge,
                ];
            }),
        ];
    });

    // Retourner les données filtrées
    return response()->json(['tontines' => $data], 200);
}



public function programTirage(Request $request, Tontine $tontine)
{
    $createur = $tontine->users()
        ->wherePivot('badge', 'proprio')
        ->first();

    if (!$createur || $createur->id !== auth()->id()) {
        return response()->json(['message' => 'Seul le créateur de la tontine peut effectuer le tirage.'], 403);
    }

    // Validation des données
    $request->validate([
        'user_id' => 'required|exists:users,id', // ID du membre qui doit recevoir
        'date_versement' => 'required|date|after_or_equal:today', // Date du versement
    ]);

    // Vérifier si l'utilisateur appartient à la tontine
    $user = $tontine->users()->find($request->user_id);
    if (!$user) {
        return response()->json(['message' => 'Cet utilisateur n\'appartient pas à cette tontine.'], 404);
    }

    // Vérifier si cet utilisateur a déjà été sélectionné pour un tirage dans cette tontine
    $existingTirage = Tirage::where('tontine_id', $tontine->id)
        ->where('user_id', $request->user_id)
        ->exists();

    if ($existingTirage) {
        return response()->json(['message' => 'Cet utilisateur a déjà été sélectionné pour un tirage dans cette tontine.'], 422);
    }

    // Enregistrer le tirage programmé
    $tirage = Tirage::create([
        'tontine_id' => $tontine->id,
        'user_id' => $request->user_id,
        'date_versement' => $request->date_versement,
    ]);

    return response()->json(['message' => 'Tirage programmé avec succès.', 'tirage' => $tirage], 201);
}



public function completeTirage(Request $request, Tontine $tontine, Tirage $tirage)
{
    // Vérifiez que le tirage appartient à la tontine
    
    
    
    if ($tirage->tontine_id !== $tontine->id) {
        return response()->json(['message' => 'Ce tirage n\'appartient pas à cette tontine.'], 403);
    }
    // Vérifier si l'utilisateur appartient à la tontine
    $user = $tontine->users()->find($request->user_id);
     $createur = $tontine->users()
        ->wherePivot('badge', 'proprio')
        ->first();

    if (!$createur || $createur->id !== auth()->id()) {
        return response()->json(['message' => 'Seul le créateur de la tontine peut effectuer le tirage.'], 403);
    }


    // Marquez le tirage comme complété
    $tirage->update(['status' => Tirage::STATUS_COMPLETED]);

    return response()->json([
        'message' => 'Le tirage a été marqué comme complété.',
        'tirage' => $tirage,
    ]);
}


public function addParticipant(Request $request, Tontine $tontine)
{
    // Valider l'entrée (l'identifiant de l'utilisateur à ajouter)
    $request->validate([
        'user_id' => 'required|exists:users,id',
    ]);

   
    
     $createur = $tontine->users()
        ->wherePivot('badge', 'proprio')
        ->first();

    if (!$createur || $createur->id !== auth()->id()) {
        return response()->json(['message' => 'Seul le créateur de la tontine peut ajouter un participant.'], 403);
    }
    
     if ($tontine->users->count() >= $tontine->nombre_personne) {
            return response()->json(['message' => 'Le nombre maximum de participants est atteint.'], 422);
        }

    $newParticipantId = $request->input('user_id');

    // Vérifier si l'utilisateur est déjà membre de la tontine
    if ($tontine->users()->where('user_id', $newParticipantId)->exists()) {
        return response()->json(['message' => 'Cet utilisateur est déjà participant à la tontine.'], 422);
    }

    // Ajouter le participant avec un badge par défaut (par exemple, "membre")
    $tontine->users()->attach($newParticipantId, ['badge' => 'membre']);

    return response()->json(['message' => 'Participant ajouté avec succès.'], 200);
}


}
