<?php

namespace App\Services;

use App\Models\Contribution;
use App\Models\User;

class LedgerService
{
    public function rowsFor(User $user): array
    {
        $tontineIds = $user->tontines()->pluck('tontines.id');

        return Contribution::with(['user', 'tontine'])
            ->whereIn('tontine_id', $tontineIds)
            ->latest('id')
            ->limit(200)
            ->get()
            ->map(fn (Contribution $c) => $this->row($c))
            ->all();
    }

    public function row(Contribution $contribution): array
    {
        $date = $contribution->date_contribution
            ? \Illuminate\Support\Carbon::parse($contribution->date_contribution)->format('d/m/Y')
            : '';

        return [
            'id' => $contribution->id,
            'date' => $date,
            'tontine' => $contribution->tontine->nom ?? '',
            'membre' => trim(($contribution->user->prenom ?? '').' '.($contribution->user->nom ?? '')),
            'montant' => $contribution->montant,
            'statut' => $contribution->statut ?? '',
            'provider' => $contribution->provider,
        ];
    }

    public function formatLine(array $row): string
    {
        return trim(sprintf(
            '%s | %s | %s | %s FCFA | %s',
            $row['date'] ?? '',
            $row['tontine'] ?? '',
            $row['membre'] ?? '',
            $row['montant'] ?? '',
            $row['statut'] ?? ''
        ));
    }

    public function pdf(array $rows): string
    {
        $lines = ['SUNU NATT', 'Tableau de suivi', now()->format('d/m/Y'), ''];
        foreach (array_slice($rows, 0, 32) as $row) {
            $lines[] = $this->formatLine($row);
        }

        return app(ReceiptPdf::class)->renderLines($lines);
    }
}
