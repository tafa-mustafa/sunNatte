<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Tontine;
use App\Models\Transaction;
use App\Models\Contribution;
use Illuminate\Http\Request;
use App\Services\TarifService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class ContributionController extends Controller
{
    public function contribute(Request $request, Tontine $tontine)
    {
        $user = auth()->user();

        // Montant à contribuer (par personne)
        $montant = round($tontine->montant / max($tontine->nombre_personne, 1), 2);

        try {
            // Calculer la commission dynamique pour une cotisation
            $commission = TarifService::calculerCommission($montant, 'cotisation');
            $montant_net = round($montant - $commission, 2);

            // Créer la session de paiement via l'API Wave
            $response = Http::withHeaders([
                "Authorization" => "Bearer " . env('WAVE_API_KEY'),
                "Content-Type" => "application/json"
            ])->post("https://api.wave.com/v1/checkout/sessions", [
                        "amount" => $montant_net,
                        "currency" => "XOF",
                        "error_url" => route('payment.error', [], true),
                        "success_url" => route('payment.success', [], true),
                    ]);

            if ($response->failed()) {
                Log::error('Wave API error (contribute): ' . $response->body());
                return response()->json(['error' => 'Erreur lors de la création de la session de paiement.'], 500);
            }

            $checkout_session = $response->json();

            if (!isset($checkout_session['wave_launch_url'])) {
                return response()->json(['error' => 'Réponse de l\'API Wave invalide.'], 500);
            }

            DB::beginTransaction();

            Transaction::create([
                'user_id' => $user->id,
                'tontine_id' => $tontine->id,
                'session_id' => $checkout_session['id'],
                'montant' => $montant,
                'commission' => $commission,
                'statut' => 'pending',
                'type' => 'cotisation',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Session de paiement créée avec succès.',
                'wave_launch_url' => $checkout_session['wave_launch_url'],
                'session_id' => $checkout_session['id'],
                'amount' => $montant_net,
                'original_amount' => $montant,
                'commission' => $commission,
                'currency' => $checkout_session['currency'],
                'checkout_status' => $checkout_session['checkout_status'],
                'payment_status' => $checkout_session['payment_status'],
                'when_created' => $checkout_session['when_created'],
                'when_expires' => $checkout_session['when_expires'],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur ContributionController (contribute): ' . $e->getMessage());
            return response()->json(['error' => 'Une erreur est survenue : ' . $e->getMessage()], 500);
        }
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




    public function getTokens(Request $request)
    {
        // ✅ Valider les entrées
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1', // Le montant doit être supérieur à 0
            'currency' => 'nullable|string|in:XOF,USD,EUR', // Devise autorisée
        ]);

        $amount = 10;
        $currency = 'XOF'; // Devise par défaut : XOF

        // ✅ Vérifier si la clé API existe
        $waveApiKey = env(key: "WAVE_API_KEY");


        if (empty($waveApiKey)) {
            return response()->json([
                'error' => 'Clé API Wave manquante.',
            ], 500);
        }

        try {
            // En-têtes nécessaires pour l'API Wave
            $headers = [
                "Authorization" => "Bearer " . $waveApiKey,
                "Content-Type" => "application/json",
            ];

            // Données envoyées dans le corps de la requête
            $data = [
                "amount" => (string) $amount, // ⚠️ Wave attend une string
                "currency" => $currency,
            ];

            // Envoyer la requête à l'API Wave
            $response = Http::withHeaders($headers)
                ->post("https://api.wave.com/v1/checkout/sessions", $data);

            // ✅ Vérifier la réponse
            if ($response->successful()) {
                return response()->json([
                    'message' => 'Token récupéré avec succès.',
                    'data' => $response->json()
                ]);
            }

            // ❌ Cas d'erreur : retour API non successful
            return response()->json([
                'error' => 'Impossible de récupérer le token.',
                'details' => $response->json(),
            ], $response->status());

        } catch (\Exception $e) {
            // ❌ Gestion des exceptions Laravel/Http
            return response()->json([
                'error' => 'Erreur lors de la communication avec l’API Wave.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }



    public function initiateContribution(Request $request, Tontine $tontine)
    {
        $user = auth()->user();

        // ✅ Calcul du montant par personne
        $montant = round($tontine->montant / max($tontine->nombre_personne, 1), 2);

        try {
            // 👉 Calculer la commission dynamique (type: cotisation)
            $commission = TarifService::calculerCommission($montant, 'cotisation');

            // ✅ Déduire la commission pour obtenir le montant net
            $montant_net = round($montant - $commission, 2);

            // Vérifier que le montant net est valide
            if ($montant_net <= 0) {
                return response()->json([
                    'error' => 'Le montant net après déduction de la commission est invalide.'
                ], 422);
            }

            // ✅ Créer la session de paiement via l'API Wave
            $response = Http::withHeaders([
                "Authorization" => "Bearer " . env('WAVE_API_KEY'),
                "Content-Type" => "application/json"
            ])->post("https://api.wave.com/v1/checkout/sessions", [
                        "amount" => $montant_net,
                        "currency" => "XOF",
                        "error_url" => route('payment.error', [], true),
                        "success_url" => route('payment.success', [], true),
                    ]);

            // Vérifier si l'API Wave a échoué
            if ($response->failed()) {
                \Log::error('Wave API error (initiateContribution): ' . $response->body());
                return response()->json(['error' => 'Erreur lors de la création de la session de paiement.'], 500);
            }

            $checkout_session = $response->json();

            // Vérifier si l'URL de lancement est présente
            if (empty($checkout_session['wave_launch_url'])) {
                return response()->json(['error' => 'Réponse de l\'API Wave invalide.'], 500);
            }

            // ✅ Retourner la réponse avec tous les détails
            return response()->json([
                'message' => 'Session de paiement créée avec succès.',
                'wave_launch_url' => $checkout_session['wave_launch_url'],
                'session_id' => $checkout_session['id'],
                'amount' => $montant_net,
                'original_amount' => $montant,
                'commission' => $commission,
                'currency' => $checkout_session['currency'],
                'tontine_id' => $tontine->id
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur API Wave (initiateContribution): ' . $e->getMessage());
            return response()->json(['error' => 'Une erreur est survenue lors de la communication avec Wave.'], 500);
        }
    }





    public function confirmPayment(Request $request, Tontine $tontine)
{
    $validated = $request->validate([
        'session_id' => 'required|string',
    ]);

    $session_id = $validated['session_id'];
    $user = auth()->user();
    $dateActuelle = now();

    try {
        // Vérifier le statut du paiement via l'API Wave
        $response = Http::withHeaders([
            "Authorization" => "Bearer " . env('WAVE_API_KEY'),
            "Content-Type" => "application/json"
        ])->get("https://api.wave.com/v1/checkout/sessions/{$session_id}");

        if ($response->failed()) {
            Log::error('Wave API error (confirmPayment): ' . $response->body());
            return response()->json(['error' => 'Erreur lors de la vérification du statut de paiement.'], 500);
        }

        $payment_status = $response->json();
        $status = $payment_status['payment_status'] ?? 'unknown';
        $amount = $payment_status['amount'] ?? null;

        if ($status === 'succeeded') {
            if (!$amount || $amount <= 0) {
                return response()->json(['error' => 'Le montant du paiement est invalide.'], 422);
            }

            DB::beginTransaction();

            try {
                // Vérifier les doublons
                $transactionExiste = Contribution::where('transaction_id', $session_id)->exists();
                if ($transactionExiste) {
                    return response()->json(['message' => 'Cette transaction a déjà été confirmée.'], 200);
                }

                // Vérifier la contribution mensuelle
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

                return response()->json([
                    'message' => 'Contribution enregistrée et montant ajouté à la tontine.',
                    'tontine' => $tontine->fresh()
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erreur lors de l\'enregistrement de la contribution : ' . $e->getMessage());

                return response()->json([
                    'error' => 'Erreur lors de l\'enregistrement de la contribution.',
                    'details' => $e->getMessage()
                ], 500);
            }
        } elseif (in_array($status, ['failed', 'processing', 'pending'])) {
            return response()->json(['error' => 'Le paiement est ' . $status . '. Veuillez réessayer ou patienter.'], 400);
        } else {
            return response()->json(['error' => "Statut de paiement inconnu: {$status}"], 400);
        }
    } catch (\Exception $e) {
        Log::error('Erreur API Wave (confirmPayment): ' . $e->getMessage());
        return response()->json(['error' => 'Une erreur est survenue lors de la vérification du paiement.'], 500);
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

public function getContributionsByTontine(Tontine $tontine)
{
    $now = now();

    // Charger uniquement les contributions du mois en cours avec les infos utilisateur
    $contributions = $tontine->contributions()
        ->whereYear('date_contribution', $now->year)
        ->whereMonth('date_contribution', $now->month)
        ->with('user')
        ->get();

    if ($contributions->isEmpty()) {
        return response()->json([
            'message' => 'Aucune contribution trouvée pour cette tontine ce mois-ci.'
        ], 404);
    }

    // Calcul du total des contributions du mois
    $totalContributions = $contributions->sum('montant');

    // Formatter les données pour l’API
    $data = $contributions->map(function ($contribution) {
        return [
            'id' => $contribution->id,
            'montant' => $contribution->montant,
            'date_contribution' => $contribution->date_contribution,
            'transaction_id' => $contribution->transaction_id,
            'user' => [
                'id' => $contribution->user->id,
                'nom' => $contribution->user->nom,
                'prenom' => $contribution->user->prenom,
                'phone' => $contribution->user->phone,
            ]
        ];
    });

    return response()->json([
        'tontine' => [
            'id' => $tontine->id,
            'nom' => $tontine->nom,
            'mois' => $now->format('F Y'), // ex: "Juillet 2025"
            'montant_attendu' => $tontine->montant,
            'total_contributions_mois' => $totalContributions,
            'nombre_contributions_mois' => $contributions->count(),
            'contributions' => $data
        ]
    ], 200);
}





}
