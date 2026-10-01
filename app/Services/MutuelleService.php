<?php

namespace App\Services;

use App\Models\Mutuelle;
use App\Models\MutuelleBeneficiaire;
use App\Models\MutuelleMouvement;
use App\Models\User;
use App\Notifications\MutuelleNotification;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class MutuelleService
{
    public function dueAmount(float $alloue, float $taux): float
    {
        return (float) round($alloue * (1 + max(0, $taux) / 100), 0);
    }

    public function parseCsv(string $contents): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($contents)) ?: [];
        if ($lines === []) {
            throw new InvalidArgumentException('Le fichier est vide.');
        }

        $rows = [];
        foreach ($lines as $index => $line) {
            if (trim($line) === '') {
                continue;
            }
            $cols = array_map('trim', str_getcsv($line, ';'));
            if (count($cols) < 3) {
                $cols = array_map('trim', str_getcsv($line, ','));
            }
            if ($index === 0 && $this->isHeader($cols)) {
                continue;
            }
            if (count($cols) < 3) {
                throw new InvalidArgumentException('Ligne '.($index + 1).' : colonnes nom, telephone, montant requises.');
            }
            $rows[] = [
                'nom' => $cols[0],
                'phone' => preg_replace('/\s+/', '', $cols[1]),
                'montant' => (float) str_replace([' ', ','], ['', '.'], $cols[2]),
            ];
        }

        return $rows;
    }

    public function normalizeBeneficiaries(array $items, float $taux): array
    {
        if (count($items) < 1 || count($items) > 50) {
            throw new InvalidArgumentException('La mutuelle accepte entre 1 et 50 bénéficiaires.');
        }

        $normalized = [];
        foreach ($items as $item) {
            $nom = trim((string) ($item['nom'] ?? ''));
            $phone = preg_replace('/\s+/', '', (string) ($item['phone'] ?? $item['telephone'] ?? ''));
            $montant = (float) ($item['montant'] ?? $item['montant_alloue'] ?? 0);
            if ($nom === '' || $phone === '' || $montant <= 0) {
                throw new InvalidArgumentException('Chaque bénéficiaire doit avoir un nom, un téléphone et un montant.');
            }
            $user = User::where('phone', $phone)->first();
            $normalized[] = [
                'nom' => $nom,
                'phone' => $phone,
                'user_id' => $user?->id,
                'montant_alloue' => $montant,
                'montant_du' => $this->dueAmount($montant, $taux),
                'montant_rembourse' => 0,
                'statut' => 'pending',
            ];
        }

        return $normalized;
    }

    public function preview(Mutuelle $mutuelle): array
    {
        $beneficiaires = $mutuelle->beneficiaires;
        $alloue = (float) $beneficiaires->sum('montant_alloue');
        $du = (float) $beneficiaires->sum('montant_du');
        $rembourse = (float) $beneficiaires->sum('montant_rembourse');

        return [
            'mutuelle' => [
                'id' => $mutuelle->id,
                'nom' => $mutuelle->nom,
                'objectif' => $mutuelle->objectif,
                'taux_interet' => (float) $mutuelle->taux_interet,
                'duree_mois' => (int) $mutuelle->duree_mois,
                'statut' => $mutuelle->statut,
                'fonds_credite' => (float) $mutuelle->fonds_credite,
            ],
            'totaux' => [
                'alloue' => $alloue,
                'du' => $du,
                'rembourse' => $rembourse,
                'restant' => max(0, $du - $rembourse),
                'interets' => max(0, $du - $alloue),
            ],
            'beneficiaires' => $beneficiaires->map(fn (MutuelleBeneficiaire $b) => [
                'id' => $b->id,
                'nom' => $b->nom,
                'phone' => $b->phone,
                'montant_alloue' => (float) $b->montant_alloue,
                'montant_du' => (float) $b->montant_du,
                'montant_rembourse' => (float) $b->montant_rembourse,
                'restant' => $b->restant(),
                'statut' => $b->statut,
            ])->values(),
        ];
    }

    public function canClose(Mutuelle $mutuelle): bool
    {
        if ($mutuelle->beneficiaires->isEmpty()) {
            return false;
        }

        return $mutuelle->beneficiaires->every(fn (MutuelleBeneficiaire $b) => $b->restant() <= 0);
    }

    public function applyRepayment(MutuelleBeneficiaire $beneficiaire, float $montant): MutuelleBeneficiaire
    {
        if ($montant <= 0) {
            throw new InvalidArgumentException('Le montant du remboursement doit être positif.');
        }
        $restant = $beneficiaire->restant();
        if ($montant > $restant) {
            throw new InvalidArgumentException('Le montant dépasse le solde à rembourser ('.$restant.' FCFA).');
        }

        $beneficiaire->montant_rembourse += $montant;
        $beneficiaire->statut = $beneficiaire->restant() <= 0 ? 'solde' : 'en_cours';

        return $beneficiaire;
    }

    public function syncBeneficiaries(Mutuelle $mutuelle, array $items): void
    {
        if (! in_array($mutuelle->statut, ['draft'], true)) {
            throw new InvalidArgumentException('Les bénéficiaires ne peuvent plus être modifiés après alimentation.');
        }

        $mutuelle->beneficiaires()->delete();
        foreach ($this->normalizeBeneficiaries($items, (float) $mutuelle->taux_interet) as $row) {
            $mutuelle->beneficiaires()->create($row);
        }
    }

    public function initiateFund(Mutuelle $mutuelle, User $user, string $provider = 'wave'): array
    {
        if ($mutuelle->statut !== 'draft') {
            throw new InvalidArgumentException('Cette mutuelle a déjà été alimentée.');
        }
        $total = (float) $mutuelle->beneficiaires()->sum('montant_alloue');
        if ($total <= 0) {
            throw new InvalidArgumentException('Ajoutez des bénéficiaires avant d\'alimenter la mutuelle.');
        }
        if (strtolower($provider) === 'orange_money') {
            return [
                'status' => 501,
                'body' => ['code' => 'OM_NOT_AVAILABLE', 'message' => 'Orange Money sera disponible prochainement. Utilisez Wave.'],
            ];
        }

        $key = config('payments.wave.key');
        if (! $key) {
            return ['status' => 500, 'body' => ['error' => 'Clé API Wave manquante.']];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$key,
            'Content-Type' => 'application/json',
        ])->post(config('payments.wave.checkout'), [
            'amount' => (string) $total,
            'currency' => config('payments.currency', 'XOF'),
            'error_url' => route('payment.error', [], true),
            'success_url' => route('payment.success', [], true),
            'client_reference' => 'mutuelle:'.$mutuelle->id.':fund',
        ]);

        if ($response->failed() || ! isset($response['wave_launch_url'], $response['id'])) {
            return ['status' => 500, 'body' => ['error' => 'Erreur session Wave.', 'details' => $response->json()]];
        }

        $mutuelle->session_id = $response['id'];
        $mutuelle->save();

        MutuelleMouvement::updateOrCreate(
            ['session_id' => $response['id']],
            [
                'mutuelle_id' => $mutuelle->id,
                'type' => 'in',
                'montant' => $total,
                'provider' => 'wave',
                'statut' => 'pending',
            ]
        );

        return [
            'status' => 200,
            'body' => [
                'message' => 'Alimentez la mutuelle via Wave.',
                'wave_launch_url' => $response['wave_launch_url'],
                'session_id' => $response['id'],
                'amount' => $total,
            ],
        ];
    }

    public function markFunded(Mutuelle $mutuelle, float $amount): Mutuelle
    {
        if ($mutuelle->statut !== 'draft') {
            return $mutuelle;
        }

        $mutuelle->statut = 'active';
        $mutuelle->fonds_credite = $amount;
        $mutuelle->save();
        $mutuelle->mouvements()->where('type', 'in')->update(['statut' => 'succeeded']);

        foreach ($mutuelle->beneficiaires as $beneficiaire) {
            $payout = app(WavePayoutService::class)->send(
                $beneficiaire->phone,
                (float) $beneficiaire->montant_alloue,
                'mutuelle-out-'.$mutuelle->id.'-'.$beneficiaire->id
            );

            $mutuelle->mouvements()->create([
                'beneficiaire_id' => $beneficiaire->id,
                'type' => 'out',
                'montant' => $beneficiaire->montant_alloue,
                'provider' => 'wave',
                'session_id' => $payout['id'],
                'statut' => $payout['ok'] ? 'succeeded' : 'failed',
            ]);

            $beneficiaire->statut = $payout['ok'] ? 'distribue' : 'payout_failed';
            $beneficiaire->save();

            $sms = 'Vous avez reçu '.$beneficiaire->montant_alloue.' FCFA via la mutuelle '.$mutuelle->nom.' (Sunu Natt).';
            if ($beneficiaire->user) {
                $this->dispatch($beneficiaire->user, 'distribution_mutuelle', $sms, $mutuelle);
            } else {
                app(SmsGateway::class)->send($beneficiaire->phone, $sms);
            }
        }

        if ($mutuelle->createur) {
            $this->dispatch(
                $mutuelle->createur,
                'mutuelle_funded',
                'Mutuelle '.$mutuelle->nom.' alimentée. Fonds distribués aux bénéficiaires.',
                $mutuelle
            );
        }

        return $mutuelle->fresh(['beneficiaires']);
    }

    public function settleWave(array $data): ?Mutuelle
    {
        $reference = $data['client_reference'] ?? '';
        $sessionId = $data['id'] ?? $data['checkout_session_id'] ?? null;
        $amount = (float) ($data['amount'] ?? 0);

        if (preg_match('/mutuelle:(\d+):fund/', $reference, $m)) {
            $mutuelle = Mutuelle::find($m[1]);
            if ($mutuelle) {
                return $this->markFunded($mutuelle, $amount ?: (float) $mutuelle->beneficiaires()->sum('montant_alloue'));
            }
        }

        if (preg_match('/mutuelle:(\d+):repay:(\d+)/', $reference, $m)) {
            $beneficiaire = MutuelleBeneficiaire::where('mutuelle_id', $m[1])->find($m[2]);
            if ($beneficiaire && $amount > 0) {
                $this->applyRepayment($beneficiaire, $amount);
                $beneficiaire->save();
                $this->recordRepayment($beneficiaire, $amount, $sessionId);
                $this->maybeClose($beneficiaire->mutuelle);

                return $beneficiaire->mutuelle;
            }
        }

        if ($sessionId) {
            $mutuelle = Mutuelle::where('session_id', $sessionId)->first();
            if ($mutuelle) {
                return $this->markFunded($mutuelle, $amount ?: (float) $mutuelle->fonds_credite);
            }
        }

        return null;
    }

    public function maybeClose(Mutuelle $mutuelle): void
    {
        $mutuelle->load('beneficiaires');
        if (! $this->canClose($mutuelle) || $mutuelle->statut === 'closed') {
            return;
        }

        $retour = (float) $mutuelle->beneficiaires->sum('montant_rembourse');
        $payout = ['ok' => false, 'id' => null];
        if ($mutuelle->createur?->phone) {
            $payout = app(WavePayoutService::class)->send(
                $mutuelle->createur->phone,
                $retour,
                'mutuelle-close-'.$mutuelle->id
            );
        }

        $mutuelle->statut = $payout['ok'] ? 'closed' : 'closing';
        $mutuelle->save();
        $mutuelle->mouvements()->create([
            'type' => 'payout_createur',
            'montant' => $retour,
            'provider' => 'wave',
            'session_id' => $payout['id'],
            'statut' => $payout['ok'] ? 'succeeded' : 'failed',
        ]);
        $message = $payout['ok']
            ? 'Mutuelle '.$mutuelle->nom.' clôturée avec succès.'
            : 'Mutuelle '.$mutuelle->nom.' soldée. Le virement vers votre Wave est en attente.';
        if ($mutuelle->createur) {
            $this->dispatch($mutuelle->createur, 'mutuelle_closed', $message, $mutuelle);
        }
    }

    public function recordRepayment(MutuelleBeneficiaire $beneficiaire, float $montant, ?string $sessionId): void
    {
        MutuelleMouvement::create([
            'mutuelle_id' => $beneficiaire->mutuelle_id,
            'beneficiaire_id' => $beneficiaire->id,
            'type' => 'repay',
            'montant' => $montant,
            'provider' => 'wave',
            'session_id' => $sessionId,
            'statut' => 'succeeded',
        ]);

        if ($beneficiaire->mutuelle->createur) {
            $this->dispatch(
                $beneficiaire->mutuelle->createur,
                'remboursement_mutuelle',
                'Remboursement reçu de '.$beneficiaire->nom.' ('.$montant.' FCFA).',
                $beneficiaire->mutuelle
            );
        }
        if ($beneficiaire->user) {
            $this->dispatch(
                $beneficiaire->user,
                'remboursement_mutuelle',
                'Votre remboursement de '.$montant.' FCFA a été enregistré.',
                $beneficiaire->mutuelle
            );
        }
    }

    private function dispatch(User $user, string $event, string $message, Mutuelle $mutuelle): void
    {
        app(NotificationDispatcher::class)->send(
            $user,
            $event,
            $message,
            new MutuelleNotification($mutuelle, $message, $event)
        );
    }

    private function isHeader(array $cols): bool
    {
        $first = strtolower($cols[0] ?? '');

        return in_array($first, ['nom', 'name', 'beneficiaire'], true);
    }
}
