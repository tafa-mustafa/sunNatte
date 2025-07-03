<?php

namespace Database\Seeders;

use App\Models\Tarif;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TarifSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cotisation : 1.5% par défaut
        Tarif::create([
            'type' => 'cotisation',
            'pourcentage' => 1.5
        ]);

        // Retrait : grilles en fonction du montant
        Tarif::insert([
            [
                'type' => 'retrait',
                'min' => 0,
                'max' => 49999.99,
                'pourcentage' => 5.0
            ],
            [
                'type' => 'retrait',
                'min' => 50000,
                'max' => 100000,
                'pourcentage' => 4.5
            ],
            [
                'type' => 'retrait',
                'min' => 100000.01,
                'max' => null,
                'pourcentage' => 4.0
            ],
        ]);
    }
    
}
