<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom');
            $table->string('phone');
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('adresse')->nullable();
            $table->string('profession')->nullable();
            $table->string('avatar')->default('avatars/pp.png');
            $table->string('num_cni')->nullable();
            $table->string('num_passport')->nullable();
            $table->string('reset_code')->nullable();
            $table->string('preuve_fond')->nullable();
            $table->string('bank')->nullable();
            $table->string('phone2')->nullable();
            $table->boolean('statut')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
        
       
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
