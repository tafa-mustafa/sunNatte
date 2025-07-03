<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Tontine;
use App\Models\Contribution;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ContributionController extends Controller
{
   public function contribute(Request $request, Tontine $tontine)
{
    $user = auth()->user();
    $montant = round($tontine->montant / $tontine->nombre_personne, 2);

    // Créer la session de paiement via l'API Wave
    $response = Http::withHeaders([
        "Authorization" => "Bearer " . env('WAVE_API_KEY'),
        "Content-Type" => "application/json"
    ])->post("https://api.wave.com/v1/checkout/sessions", [
        "amount" => round($montant * 0.96, 2),
        "currency" => "XOF",
        "error_url" => route('payment.error', [], true), // Forcer HTTPS
        "success_url" => route('payment.success', [], true), // Forcer HTTPS
    ]);

    // Vérifier si la requête a échoué
    if ($response->failed()) {
        return response()->json(['error' => 'Erreur lors de la création de la session de paiement.'], 500);
    }

    $checkout_session = $response->json();

    // Vérifier si 'wave_launch_url' est présent dans la réponse
    if (!isset($checkout_session['wave_launch_url'])) {
        return response()->json(['error' => 'Réponse de l\'API Wave invalide.'], 500);
    }

    // Utiliser une transaction pour sécuriser l'enregistrement des données
    DB::beginTransaction();

    try {
        // Créer la contribution
        $contribution = Contribution::create(attributes: [
            'user_id' => $user->id,
            'tontine_id' => $tontine->id,
            'montant' => $montant,
        ]);

        DB::commit();

        // Renvoyer les données pertinentes à l'utilisateur
        return response()->json([
            'message' => 'Session de paiement créée avec succès.',
            'wave_launch_url' => $checkout_session['wave_launch_url'],
            'session_id' => $checkout_session['id'],
            'amount' => $checkout_session['amount'],
            'currency' => $checkout_session['currency'],
            'checkout_status' => $checkout_session['checkout_status'],
            'payment_status' => $checkout_session['payment_status'],
            'when_created' => $checkout_session['when_created'],
            'when_expires' => $checkout_session['when_expires'],
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => 'Erreur lors de l\'enregistrement de la contribution.'], 500);
    }
}

  public function getContributionsByTontine(Tontine $tontine)
{
    $user = auth()->user();

    // Récupérer les contributions de l'utilisateur liées à cette tontine
    $contributions = $user->contributions()
        ->where('tontine_id', $tontine->id) // Filtrer par la tontine spécifique
        ->with('tontine:id,nom') // Charger uniquement les champs nécessaires de la tontine
        ->get(['id', 'montant', 'date_contribution', 'tontine_id']); // Sélectionner les champs nécessaires

    if ($contributions->isEmpty()) {
        return response()->json(['message' => 'Aucune contribution trouvée pour cette tontine.'], 404);
    }

    // Transformer les données pour inclure les noms des tontines
    $contributions = $contributions->map(function ($contribution) {
        return [
            'id' => $contribution->id,
            'montant' => $contribution->montant,
            'date_contribution' => $contribution->date_contribution instanceof \Carbon\Carbon 
                ? $contribution->date_contribution->format('Y-m-d') 
                : $contribution->date_contribution,
            'tontine_name' => $contribution->tontine->nom,
        ];
    });

    return response()->json(['contributions' => $contributions], 200);
}


    public function success(Request $request)
    {

        return response()->json('Paiement réussi !');
    }

    public function error(Request $request)
    {

        return response()->json('Erreur de paiement !');
    }
    
    
    
      public function getToken(Request $request)
    {
        // En-têtes nécessaires pour l'API Wave
        $headers = [
            "Authorization" => "Bearer " . env('WAVE_API_KEY'),
            "Content-Type" => "application/json",
        ];

        // Données envoyées dans le corps de la requête
        $data = [
            "amount" => $request->input('amount', 1000), // Exemple : Montant par défaut 1000
            "currency" => $request->input('currency', 'XOF'), // Exemple : Devise par défaut XOF
            "error_url" => route('payment.error'),
            "success_url" => route('payment.success'),
        ];

        // Envoyer la requête à l'API Wave
        $response = Http::withHeaders($headers)->post("https://api.wave.com/v1/checkout/sessions", $data);

        // Vérifier la réponse
        if ($response->successful()) {
            return response()->json([
                'message' => 'Token récupéré avec succès.',
                'data' => $response->json()
            ]);
        }

        return response()->json([
            'error' => 'Impossible de récupérer le token.',
            'details' => $response->json(),
        ], $response->status());
    }
    
    
    
    public function initiateContribution(Request $request, Tontine $tontine)
{
    $user = auth()->user();
    $montant = round($tontine->montant / $tontine->nombre_personne, 2);

    $response = Http::withHeaders([
        "Authorization" => "Bearer " . env('WAVE_API_KEY'),
        "Content-Type" => "application/json"
    ])->post("https://api.wave.com/v1/checkout/sessions", [
        "amount" => $montant,
        "currency" => "XOF",
        "error_url" => route('payment.error', [], true),
        "success_url" => route('payment.success', [], true),
    ]);

    if ($response->failed()) {
        return response()->json(['error' => 'Erreur lors de la création de la session de paiement.'], 500);
    }

    $checkout_session = $response->json();

    if (!isset($checkout_session['wave_launch_url'])) {
        return response()->json(['error' => 'Réponse de l\'API Wave invalide.'], 500);
    }

    // Renvoyer les informations de session au client
    return response()->json([
        'message' => 'Session de paiement créée avec succès.',
        'wave_launch_url' => $checkout_session['wave_launch_url'],
        'session_id' => $checkout_session['id'], // À stocker côté client pour validation ultérieure
        'amount' => $checkout_session['amount'],
        'currency' => $checkout_session['currency'],
        'tontine_id'=> $tontine['id']
    ]);
}



public function confirmPayment(Request $request, Tontine $tontine)
{
    // Valider les données entrantes
    $validated = $request->validate([
        'session_id' => 'required|string', // session_id est obligatoire
    ]);

    $session_id = $validated['session_id'];
    $user = auth()->user();
    $dateActuelle = now();

    // Vérifier le statut de paiement via l'API Wave
    $response = Http::withHeaders([
        "Authorization" => "Bearer " . env('WAVE_API_KEY'),
        "Content-Type" => "application/json"
    ])->get("https://api.wave.com/v1/checkout/sessions/{$session_id}");

    if ($response->failed()) {
        return response()->json(['error' => 'Erreur lors de la vérification du statut de paiement.'], 500);
    }

    $payment_status = $response->json();
    $status = $payment_status['payment_status'] ?? 'unknown';
    $amount = $payment_status['amount'] ?? null;

    // Vérifier le statut du paiement
    if ($status === 'succeeded') {
        if (!$amount || $amount <= 0) {
            return response()->json(['error' => 'Le montant du paiement est invalide.'], 422);
        }

        DB::beginTransaction();
        try {
            // Vérifier si l'utilisateur a déjà contribué ce mois-ci pour cette tontine
            $contributionExistante = $tontine->contributions()
                ->where('user_id', $user->id)
                ->whereYear('date_contribution', $dateActuelle->year)
                ->whereMonth('date_contribution', $dateActuelle->month)
                ->exists();

            if ($contributionExistante) {
                return response()->json(['message' => 'Vous avez déjà contribué ce mois-ci à cette tontine.'], 422);
            }

            // Enregistrer la contribution
            $tontine->contributions()->create([
                'user_id' => $user->id,
                'montant' => $amount,
                'date_contribution' => $dateActuelle,
                'transaction_id' => $session_id,
            ]);

            DB::commit();
            return response()->json(['message' => 'Contribution enregistrée avec succès.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            // Ajouter un log pour le débogage
            Log::error('Erreur lors de l\'enregistrement de la contribution : ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de l\'enregistrement de la contribution.'.$e], 500);
        }
    } elseif (in_array($status, ['failed', 'processing', 'pending'])) {
        return response()->json(['error' => 'Le paiement est ' . $status . '. Veuillez réessayer ou patienter.'], 400);
    } else {
        return response()->json(['error' => "Statut de paiement inconnu: {$status}"], 400);
    }
}



public function getContributionDetail($id)
{
    $user = auth()->user();

    // Rechercher la contribution en s'assurant qu'elle appartient à l'utilisateur connecté
    $contribution = $user->contributions()
        ->where('id', $id)
        ->with('tontine:id,nom') // Charger uniquement les champs nécessaires de la tontine
        ->first(['id', 'montant', 'date_contribution', 'tontine_id']); // Sélectionner les champs nécessaires

    // Vérifier si la contribution existe
    if (!$contribution) {
        return response()->json(['message' => 'Contribution introuvable ou inaccessible.'], 404);
    }

    // Assurez-vous que la date est bien une instance Carbon
    $date_contribution = $contribution->date_contribution instanceof \Carbon\Carbon 
        ? $contribution->date_contribution->format('Y-m-d') 
        : $contribution->date_contribution;

    // Construire la réponse
    $contributionDetail = [
        'id' => $contribution->id,
        'montant' => $contribution->montant,
        'date_contribution' => $date_contribution,
        'tontine_name' => $contribution->tontine->nom,
    ];

    return response()->json( $contributionDetail);
}




}
