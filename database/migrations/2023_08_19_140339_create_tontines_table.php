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
        Schema::create('tontines', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('nombre_personne');
            $table->string('type');
            $table->string('duree');
            $table->string('montant');
            $table->string('type_tirage')->nullable();
            $table->text('tirage')->nullable();
            $table->string('code_adhesion')->unique();
            $table->string('date_demarrage')->nullable();
            $table->string('date_fin')->nullable();
            $table->text('description')->nullable();
            $table->boolean('statut')->nullable();
            $table->unsignedBigInteger('materiel_id')->nullable();
            $table->foreign('materiel_id')->references('id')->on('materiels');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tontines');
    }
};
