<?php

namespace App\Services;

use App\Models\Contribution;
use App\Models\Tontine;
use App\Models\User;
use App\Notifications\ContributionPaidNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PaymentService
{
    public function __construct(private ReceiptPdf $receipts)
    {
    }

    public function amountDue(Tontine $tontine): float
    {
        $n = max(1, (int) $tontine->nombre_personne);

        return round(((float) $tontine->montant) / $n, 0);
    }

    public function verifyWaveSignature(string $payload, ?string $header, ?string $secret = null): bool
    {
        $secret = $secret ?? config('payments.wave.webhook_secret');
        if (! $secret) {
            return app()->environment('testing');
        }
        if (! $header) {
            return false;
        }

        $timestamp = null;
        $hash = null;
        foreach (explode(',', $header) as $part) {
            [$key, $value] = array_pad(explode('=', trim($part), 2), 2, null);
            if ($key === 't') {
                $timestamp = $value;
            }
            if ($key === 'v1') {
                $hash = $value;
            }
        }

        if (! $timestamp || ! $hash) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

        return hash_equals($expected, $hash);
    }

    public function initiate(User $user, Tontine $tontine, string $provider = 'wave'): array
    {
        $provider = strtolower($provider);
        if ($provider === 'orange_money') {
            return [
                'status' => 501,
                'body' => [
                    'code' => 'OM_NOT_AVAILABLE',
                    'message' => 'Orange Money sera disponible prochainement. Utilisez Wave.',
                    'provider' => 'orange_money',
                ],
            ];
        }
        if ($provider !== 'wave') {
            return [
                'status' => 422,
                'body' => ['message' => 'Moyen de paiement non supporté.', 'provider' => $provider],
            ];
        }

        $montant = $this->amountDue($tontine);
        $key = config('payments.wave.key');
        if (! $key) {
            return ['status' => 500, 'body' => ['error' => 'Clé API Wave manquante.']];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$key,
            'Content-Type' => 'application/json',
        ])->post(config('payments.wave.checkout'), [
            'amount' => (string) $montant,
            'currency' => config('payments.currency', 'XOF'),
            'error_url' => route('payment.error', [], true),
            'success_url' => route('payment.success', [], true),
            'client_reference' => 'tontine:'.$tontine->id.':user:'.$user->id,
        ]);

        if ($response->failed() || ! isset($response['wave_launch_url'], $response['id'])) {
            return [
                'status' => 500,
                'body' => ['error' => 'Erreur lors de la création de la session de paiement.', 'details' => $response->json()],
            ];
        }

        $sessionId = $response['id'];

        $contribution = Contribution::updateOrCreate(
            ['transaction_id' => $sessionId],
            [
                'user_id' => $user->id,
                'tontine_id' => $tontine->id,
                'montant' => $montant,
                'statut' => 'pending',
                'provider' => 'wave',
            ]
        );

        return [
            'status' => 200,
            'body' => [
                'message' => 'Session de paiement créée avec succès.',
                'provider' => 'wave',
                'preferred_provider' => $user->preferred_payment_method ?: 'wave',
                'wave_launch_url' => $response['wave_launch_url'],
                'session_id' => $sessionId,
                'amount' => $montant,
                'currency' => config('payments.currency', 'XOF'),
                'tontine_id' => $tontine->id,
                'contribution_id' => $contribution->id,
            ],
        ];
    }

    public function settleSession(string $sessionId, float $amount, string $provider = 'wave'): Contribution
    {
        $contribution = Contribution::where('transaction_id', $sessionId)->first();
        if (! $contribution) {
            throw new RuntimeException('Session de paiement inconnue.');
        }

        if ($contribution->statut === 'succeeded') {
            return $contribution;
        }

        $contribution->statut = 'succeeded';
        $contribution->provider = $provider;
        $contribution->montant = $amount > 0 ? $amount : $contribution->montant;
        $contribution->date_contribution = now();
        $contribution->save();

        $path = $this->storeReceipt($contribution);
        $contribution->recu_path = $path;
        $contribution->save();

        $payer = $contribution->user;
        $dispatcher = app(NotificationDispatcher::class);
        if ($payer) {
            $payer->preferred_payment_method = $provider;
            $payer->save();
            $paid = new ContributionPaidNotification($contribution, 'payer');
            $dispatcher->send($payer, 'cotisation_versee', $paid->toArray($payer)['message'], $paid);
        }

        $owner = $contribution->tontine?->users()->wherePivot('badge', 'proprio')->first();
        if ($owner && $payer && $owner->id !== $payer->id) {
            $received = new ContributionPaidNotification($contribution, 'owner');
            $dispatcher->send($owner, 'cotisation_recue', $received->toArray($owner)['message'], $received);
        }

        return $contribution->fresh(['tontine', 'user']);
    }

    public function storeReceipt(Contribution $contribution): string
    {
        $contribution->loadMissing(['user', 'tontine']);
        $pdf = $this->receipts->render([
            'reference' => 'SN-'.$contribution->id,
            'date' => $contribution->date_contribution
                ? \Illuminate\Support\Carbon::parse($contribution->date_contribution)->format('d/m/Y H:i')
                : now()->format('d/m/Y H:i'),
            'membre' => trim(($contribution->user->prenom ?? '').' '.($contribution->user->nom ?? '')),
            'tontine' => $contribution->tontine->nom ?? '',
            'montant' => $contribution->montant,
            'provider' => strtoupper((string) $contribution->provider),
        ]);

        $path = 'receipts/cotisation-'.$contribution->id.'.pdf';
        Storage::disk('public')->put($path, $pdf);

        return $path;
    }
}
