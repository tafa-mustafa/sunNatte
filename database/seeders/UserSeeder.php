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
            'nom' => 'admin',
            'prenom' => 'administrator',
            'phone' => '777000606',
            'email' => 'admin@sununat.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password@'),
            'role_id' => User::ROLE_ADMIN,
            'profession' => 'admin systems',
        ]);
    }
}
