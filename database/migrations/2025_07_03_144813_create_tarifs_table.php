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
        Schema::create('tarifs', function (Blueprint $table) {
            $table->id();

            $table->enum('type', ['cotisation', 'retrait']); // Type: cotisation ou retrait
            $table->decimal('min', 12, 2)->nullable();        // Montant minimum (pour palier)
            $table->decimal('max', 12, 2)->nullable();        // Montant maximum (pour palier)
            $table->decimal('pourcentage', 5, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarifs');
    }
};
