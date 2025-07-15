<?php

namespace App\Services;

use App\Models\Tarif;

class TarifService
{
    /**
     * Calcule la commission pour un montant donné en fonction du type de tarif.
     *
     * @param float $montant
     * @param string $type ('cotisation' ou 'retrait')
     * @return float
     * @throws \Exception
     */
    public static function calculerCommission(float $montant, string $type): float
    {
        // Récupérer le tarif dynamique
        $tarif = Tarif::where('type', $type)->first();

        if (!$tarif) {
            throw new \Exception("Tarif pour {$type} non configuré.");
        }

        // Calculer la commission en fonction du pourcentage
        $commission = round($montant * ($tarif->pourcentage / 100), 2);

        // Appliquer les plafonds min et max si définis
        if (!is_null($tarif->min) && $commission < $tarif->min) {
            $commission = $tarif->min;
        }

        if (!is_null($tarif->max) && $commission > $tarif->max) {
            $commission = $tarif->max;
        }

        return $commission;
    }
}
