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
        $utilisateur = auth()->user();

        // Récupérer les tontines associées à cet utilisateur
        $tontines = $utilisateur->tontines()->get();



        return response()->json($tontines);
    }


    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required',
            'nombre_personne' => 'required',
            'type' => 'required',
            'duree' => 'required',
            'montant' => 'required',
            'tirage' => 'required',
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
            $dateDemarrageFormat = $dateDemarrage->format('d-m-y');
            $dateFinFormat = $dateFin->format('d -m-y');

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

            $createur->adhesions()->update(['badge' => 'proprio']);
           DB::commit();

        return response()->json('tontine created !!');
            

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'Erreur lors de la création de la tontine : ' . $e->getMessage()], 500);
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

        $createur = auth()->user();

        if ($createur->id !== $tontine->createur_id) {
            return response()->json(['message' => 'Seul le créateur de la tontine peut effectuer le tirage.'], 403);
        }

        $utilisateurs = $tontine->users->all();

        if (count($utilisateurs) < $tontine->nombre_personne) {
            return response()->json(['message' => 'Le tirage ne peut pas avoir lieu car les membres ne sont pas au complet.']);
        }

        shuffle($utilisateurs);

        return response()->json(['le tirage est' => $utilisateurs]);
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


}
