<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\TirelireWithdrawalService;
use Illuminate\Http\Request;
use App\Models\Tirelire;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class TirelireController extends Controller
{
    public function __construct(private TirelireWithdrawalService $withdrawals)
    {
    }

      public function createTirelire(Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'montant' => 'required|numeric|min:1',
            'montant_objectif' => 'required|numeric|min:1',
            'objectif' => 'nullable|string|max:255',
            'date_fin' => 'required|date|after:today',
        ]);

        $user = auth()->user();

        // Créer une session de paiement via Wave
        $response = Http::withHeaders([
            "Authorization" => "Bearer " . env('WAVE_API_KEY'),
            "Content-Type" => "application/json"
        ])->post("https://api.wave.com/v1/checkout/sessions", [
                    "amount" => $request->montant,
                    "currency" => "XOF",
                    "error_url" => route('payment.error'),
                    "success_url" => route('payment.success'),
                ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Erreur lors de la création de la session de paiement.'], 500);
        }

        $checkout_session = $response->json();
        $session_id = $checkout_session['id'] ?? null;

        if (!$session_id) {
            return response()->json(['error' => 'Session de paiement invalide.'], 500);
        }

        // Créer une tirelire en statut "pending"
        $tirelire = Tirelire::create([
            'titre' => $request->titre,
            'montant' => 0, // Initialement 0, le montant sera ajouté après paiement
            'objectif' => $request->objectif,
             'montant_objectif' => $request->montant_objectif,
            'date_debut' => now(),
            'date_fin' => $request->date_fin,
            'user_id' => $user->id,
        ]);

        // Sauvegarder la transaction
        Transaction::create([
            'tirelire_id' => $tirelire->id,
            'session_id' => $session_id,
            'montant' => $request->montant,
            'statut' => 'pending',
        ]);

        return response()->json([
            'message' => 'Session de paiement créée avec succès.',
            'payment_url' => $checkout_session['wave_launch_url'],
            'tirelire' => $tirelire,
        ]);
    }
  public function addMoneyToTirelire(Request $request, Tirelire $tirelire)
{
    $request->validate([
        'montant' => 'required|numeric|min:1',
    ]);

    $user = auth()->user();

    if ($tirelire->user_id !== $user->id) {
        return response()->json(['error' => 'Vous n\'êtes pas autorisé à modifier cette tirelire.'], 403);
    }

    $montant = $request->montant;
    $commission = $montant * 0.02;

    /* 🧮 Appliquer la grille de commission
    if ($montant <= 50000) {
        $commission = $montant * 0.05; // 5%
    } elseif ($montant <= 100000) {
        $commission = $montant * 0.045; // 4.5%
    } else {
        $commission = $montant * 0.04; // 4%
    }
    */
    // 💵 Montant après commission (optionnel à afficher)
    $montantNet = $montant + $commission;

    // 🌀 Créer la session Wave
    $response = Http::withHeaders([
        "Authorization" => "Bearer " . env('WAVE_API_KEY'),
        "Content-Type" => "application/json"
    ])->post("https://api.wave.com/v1/checkout/sessions", [
        "amount" => $montantNet,
        "currency" => "XOF",
        "error_url" => route('payment.error', [], true),
        "success_url" => route('payment.success', [], true),
    ]);

    if ($response->failed()) {
        return response()->json(['error' => 'Erreur lors de la création de la session de paiement.'], 500);
    }

    $checkout_session = $response->json();
    $session_id = $checkout_session['id'] ?? null;

    if (!$session_id) {
        return response()->json(['error' => 'Session de paiement invalide.'], 500);
    }

    // 💾 Enregistrer la transaction
    $transaction = Transaction::create([
        'tirelire_id' => $tirelire->id,
        'session_id' => $session_id,
        'montant' => $montant,
        'statut' => 'pending',
    ]);

    return response()->json([
        'message' => 'Session de paiement créée',
        'checkout_url' => $checkout_session['payment_url'] ?? null,
        'commission' => $commission,
        'net' => $montantNet,
        'transaction' => $transaction
    ]);
}


    public function paymentSuccess(Request $request ,Tirelire $tirelire)
    {
        $validated = $request->validate([
            'session_id' => 'required|string',
        ]);

        $session_id = $validated['session_id'];

        // Vérifier le statut de paiement via Wave
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

        if ($status !== 'succeeded' || !$amount) {
            return response()->json(['error' => 'Le paiement a échoué ou le montant est invalide.'], 422);
        }

        // Trouver la transaction
        $transaction = Transaction::where('session_id', $session_id)->first();

        if (!$transaction) {
            return response()->json(['error' => 'Transaction non trouvée.'], 404);
        }

        // Mettre à jour la transaction et ajouter le montant à la tirelire
        $transaction->statut = 'succeeded';
        $transaction->save();

        $tirelire = $transaction->tirelire;
        $tirelire->montant += $amount;
        $tirelire->save();

        return response()->json([
            'message' => 'Paiement confirmé. Montant ajouté à la tirelire.',
            'tirelire' => $tirelire,
        ]);
    }
    
    
    public function myTirelires()
{
    // Récupérer l'utilisateur connecté
    $user = auth()->user();

    // Récupérer les tirelires de cet utilisateur
    $tirelires = $user->tirelires()->get();

    // Vérifier si des tirelires existent
    if ($tirelires->isEmpty()) {
        return response()->json(['message' => 'Aucune tirelire trouvée.'], 404);
    }

    // Retourner les tirelires avec leurs informations
    return response()->json(['tirelires' => $tirelires], 200);
}

public function showTirelire($id)
{
    // Récupérer la tirelire avec ses transactions et les utilisateurs associés
    $tirelire = Tirelire::with('transactions.user')->find($id);

    if (!$tirelire) {
        return response()->json(['error' => 'Tirelire non trouvée.'], 404);
    }

    $solde = (float) $tirelire->montant;

    return response()->json([
        'id' => $tirelire->id,
        'titre' => $tirelire->titre,
        'montant_objectif' => $tirelire->montant_objectif,
        'objectif' => $tirelire->objectif,
        'montant' => $tirelire->montant,
        'date_debut' => $tirelire->date_debut,
        'date_fin' => $tirelire->date_fin,
        'statut' => $tirelire->statut,
        'retrait' => $this->withdrawals->quote($tirelire, $solde > 0 ? $solde : 0),
        'transactions' => $tirelire->transactions->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'montant' => $transaction->montant,
                'date_transaction' => $transaction->updated_at,
            ];
        }),
    ], 200);
}

public function previewRetrait(Request $request, Tirelire $tirelire)
{
    $request->validate([
        'montant' => 'nullable|numeric|min:1',
    ]);

    $user = auth()->user();
    $montant = (float) ($request->input('montant') ?? $tirelire->montant);
    $blocked = $this->withdrawals->guard($tirelire, $user, $montant);

    if ($blocked) {
        return response()->json($blocked, $blocked['status']);
    }

    return response()->json([
        'quote' => $this->withdrawals->quote($tirelire, $montant),
        'message' => $this->withdrawals->isMature($tirelire)
            ? 'Retrait à l\'échéance : frais de 1,5 %.'
            : 'Retrait anticipé : pénalité de 5 %. Confirmez avec accept_penalty=true.',
    ]);
}

public function retirait(Request $request, Tirelire $tirelire)
{
    return $this->withdraw($request, $tirelire);
}

public function retrait_any(Request $request, Tirelire $tirelire)
{
    return $this->withdraw($request, $tirelire);
}

private function withdraw(Request $request, Tirelire $tirelire)
{
    $request->validate([
        'montant' => 'required|numeric|min:1',
        'wave_phone' => 'nullable|string',
        'accept_penalty' => 'sometimes|boolean',
    ]);

    $user = auth()->user();
    $montant = (float) $request->montant;
    $blocked = $this->withdrawals->guard($tirelire, $user, $montant);

    if ($blocked) {
        return response()->json($blocked, $blocked['status']);
    }

    $quote = $this->withdrawals->quote($tirelire, $montant);

    if ($quote['early'] && ! $request->boolean('accept_penalty')) {
        return response()->json([
            'code' => 'PENALTY_NOT_ACCEPTED',
            'message' => 'Le retrait anticipé entraîne une pénalité de '.$quote['rate_percent'].' %. Confirmez avec accept_penalty=true.',
            'quote' => $quote,
        ], 422);
    }

    try {
        DB::beginTransaction();

        $waveResponse = Http::withHeaders([
            'Authorization' => 'Bearer '.env('WAVE_API_KEY'),
            'Content-Type' => 'application/json',
            'Idempotency-Key' => Str::uuid()->toString(),
        ])->post('https://api.wave.com/v1/payout', [
            'receive_amount' => (string) $quote['net'],
            'currency' => 'XOF',
            'mobile' => $request->wave_phone ?? $user->phone,
        ]);

        if (! $waveResponse->successful()) {
            DB::rollBack();
            return response()->json([
                'error' => 'Erreur API Wave',
                'details' => $waveResponse->json(),
            ], 500);
        }

        $waveData = $waveResponse->json();
        $session_id = $waveData['id'] ?? null;

        if (! $session_id) {
            DB::rollBack();
            return response()->json(['error' => 'Session ID introuvable dans la réponse Wave.'], 500);
        }

        $tirelire->transactions()->create([
            'montant' => -$montant,
            'user_id' => $user->id,
            'session_id' => $session_id,
            'commission' => $quote['fee'],
            'statut' => 'succeeded',
            'type' => $quote['fee_type'],
        ]);

        $tirelire->montant -= $montant;
        if ((float) $tirelire->montant <= 0) {
            $tirelire->statut = 'termine';
        }
        $tirelire->save();

        DB::commit();

        return response()->json([
            'message' => $quote['early']
                ? 'Retrait anticipé effectué. Une pénalité de '.$quote['rate_percent'].' % a été appliquée.'
                : 'Retrait effectué avec succès via Wave',
            'retrait_net' => $quote['net'],
            'commission' => $quote['fee'],
            'quote' => $quote,
            'wave' => $waveData,
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Erreur retrait tirelire '.$tirelire->id.' : '.$e->getMessage());

        return response()->json(['error' => 'Erreur : '.$e->getMessage()], 500);
    }
}





}