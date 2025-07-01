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
        Schema::create('tirelires', function (Blueprint $table) {
            $table->id();
            $table->string('montant');
            $table->string('titre')->default('tirelire');
            $table->string('objectif')->nullable();
            $table->string('montant_objectif')->nullable();
            $table->string('date_debut');
            $table->string('date_fin');
            $table->unsignedBigInteger('user_id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tirelires'); // Exemple si transactions dépend de tirelires
    }
};
