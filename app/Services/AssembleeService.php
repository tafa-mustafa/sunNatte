<?php

namespace App\Services;

use App\Models\Tontine;

class AssembleeService
{
    public function lines(Tontine $tontine): array
    {
        $members = $this->rel($tontine, 'users');
        $tirages = $this->rel($tontine, 'tirages');
        $contributions = $this->rel($tontine, 'contributions');
        $total = (float) $contributions->where('statut', 'succeeded')->sum('montant');

        $lines = [
            'SUNU NATT',
            "Proces-verbal d'assemblee",
            '',
            'Tontine : '.($tontine->nom ?? ''),
            'Date : '.now()->format('d/m/Y H:i'),
            'Periode : '.$this->date($tontine->date_demarrage).' - '.$this->date($tontine->date_fin),
            'Montant pot : '.($tontine->montant ?? 0).' FCFA',
            'Membres : '.$members->count().' / '.(int) $tontine->nombre_personne,
            '',
            '--- Membres ---',
        ];

        foreach ($members as $user) {
            $nom = trim(($user->prenom ?? '').' '.($user->nom ?? ''));
            $badge = $user->pivot->badge ?? 'membre';
            $lines[] = $nom.' | '.$badge.' | '.($user->phone ?? '');
        }

        $lines[] = '';
        $lines[] = '--- Tirages ---';
        if ($tirages->isEmpty()) {
            $lines[] = 'Aucun tirage enregistre.';
        } else {
            foreach ($tirages as $tirage) {
                $nom = trim(($tirage->user->prenom ?? '').' '.($tirage->user->nom ?? ''));
                $lines[] = $this->date($tirage->date_versement).' | '.$nom.' | '.($tirage->status ?? 'pending');
            }
        }

        $lines[] = '';
        $lines[] = '--- Cotisations ---';
        foreach ($contributions->take(80) as $c) {
            $nom = trim(($c->user->prenom ?? '').' '.($c->user->nom ?? ''));
            $lines[] = $this->date($c->date_contribution).' | '.$nom.' | '.$c->montant.' FCFA | '.($c->statut ?? '');
        }
        $lines[] = '';
        $lines[] = 'Total encaisse : '.$total.' FCFA';
        $lines[] = '';
        $lines[] = 'Teknon Group SUARL - Dakar';

        return $lines;
    }

    public function pdf(Tontine $tontine): string
    {
        return app(ReceiptPdf::class)->renderLines($this->lines($tontine));
    }

    public function excelRows(Tontine $tontine): array
    {
        $rows = [];
        foreach ($this->rel($tontine, 'users') as $user) {
            $rows[] = [
                'type' => 'membre',
                'date' => '',
                'nom' => trim(($user->prenom ?? '').' '.($user->nom ?? '')),
                'detail' => $user->pivot->badge ?? 'membre',
                'montant' => '',
                'statut' => 'approved',
            ];
        }
        foreach ($this->rel($tontine, 'tirages') as $tirage) {
            $rows[] = [
                'type' => 'tirage',
                'date' => $this->date($tirage->date_versement),
                'nom' => trim(($tirage->user->prenom ?? '').' '.($tirage->user->nom ?? '')),
                'detail' => 'ordre',
                'montant' => '',
                'statut' => $tirage->status ?? 'pending',
            ];
        }
        foreach ($this->rel($tontine, 'contributions') as $c) {
            $rows[] = [
                'type' => 'cotisation',
                'date' => $this->date($c->date_contribution),
                'nom' => trim(($c->user->prenom ?? '').' '.($c->user->nom ?? '')),
                'detail' => $c->provider ?? '',
                'montant' => $c->montant,
                'statut' => $c->statut ?? '',
            ];
        }

        return $rows;
    }

    private function rel(Tontine $tontine, string $name)
    {
        if ($tontine->relationLoaded($name)) {
            return $tontine->getRelation($name);
        }
        if ($tontine->exists) {
            $tontine->loadMissing($name === 'users' ? 'users' : $name.'.user');

            return $tontine->getRelation($name);
        }

        return collect();
    }

    private function date(mixed $value): string
    {
        if (! $value) {
            return '';
        }

        return \Illuminate\Support\Carbon::parse($value)->format('d/m/Y');
    }
}
