<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'nom' => 'test',
            'prenom' => 'user',
            'telephone' => '777000606',
            'adresse' => 'Grand Yoff',
            'date_naissance' => '12/12/1999',
            'genre' => 'Homme',
            'email' => 'test@test.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password@'),
            'role_id' => User::ROLE_ADMIN,
        ]);
    }
}
