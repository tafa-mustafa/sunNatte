<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mutuelles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('createur_id');
            $table->string('nom');
            $table->string('objectif')->nullable();
            $table->decimal('taux_interet', 5, 2)->default(0);
            $table->unsignedInteger('duree_mois')->default(1);
            $table->string('statut')->default('draft');
            $table->decimal('fonds_credite', 12, 2)->default(0);
            $table->string('session_id')->nullable();
            $table->timestamps();

            $table->foreign('createur_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('mutuelle_beneficiaires', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mutuelle_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('nom');
            $table->string('phone');
            $table->decimal('montant_alloue', 12, 2);
            $table->decimal('montant_du', 12, 2);
            $table->decimal('montant_rembourse', 12, 2)->default(0);
            $table->string('statut')->default('pending');
            $table->timestamps();

            $table->foreign('mutuelle_id')->references('id')->on('mutuelles')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('mutuelle_mouvements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mutuelle_id');
            $table->unsignedBigInteger('beneficiaire_id')->nullable();
            $table->string('type');
            $table->decimal('montant', 12, 2);
            $table->string('provider')->default('wave');
            $table->string('session_id')->nullable();
            $table->string('statut')->default('pending');
            $table->timestamps();

            $table->foreign('mutuelle_id')->references('id')->on('mutuelles')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutuelle_mouvements');
        Schema::dropIfExists('mutuelle_beneficiaires');
        Schema::dropIfExists('mutuelles');
    }
};
