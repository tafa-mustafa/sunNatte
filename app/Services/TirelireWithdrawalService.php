<?php

namespace App\Services;

use App\Models\Tirelire;
use App\Models\User;
use Illuminate\Support\Carbon;

class TirelireWithdrawalService
{
    public function isMature(Tirelire $tirelire): bool
    {
        return Carbon::parse($tirelire->date_fin)->lte(now());
    }

    public function rate(Tirelire $tirelire): float
    {
        return $this->isMature($tirelire)
            ? (float) config('tirelire.maturity_fee_rate', 0.015)
            : (float) config('tirelire.early_penalty_rate', 0.05);
    }

    public function quote(Tirelire $tirelire, float $montant): array
    {
        $early = ! $this->isMature($tirelire);
        $rate = $this->rate($tirelire);
        $fee = (int) round($montant * $rate);
        $net = max(0, $montant - $fee);

        return [
            'early' => $early,
            'requires_penalty_acceptance' => $early,
            'rate' => $rate,
            'rate_percent' => round($rate * 100, 2),
            'penalty' => $early ? $fee : 0,
            'fee' => $fee,
            'fee_type' => $early ? 'penalite_retrait_anticipe' : 'frais_echeance',
            'montant' => $montant,
            'net' => $net,
            'date_fin' => $tirelire->date_fin,
            'solde' => (float) $tirelire->montant,
        ];
    }

    public function guard(Tirelire $tirelire, User $user, float $montant): ?array
    {
        if ((int) $tirelire->user_id !== (int) $user->id) {
            return ['status' => 403, 'error' => 'Vous n\'êtes pas autorisé à effectuer un retrait sur cette tirelire.'];
        }

        if ($tirelire->statut === 'termine') {
            return ['status' => 400, 'error' => 'Le retrait a déjà été effectué pour cette tirelire.'];
        }

        if ((float) $tirelire->montant < $montant) {
            return ['status' => 422, 'message' => 'Montant insuffisant'];
        }

        return null;
    }
}
