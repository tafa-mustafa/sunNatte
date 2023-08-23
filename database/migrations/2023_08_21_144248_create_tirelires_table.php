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
            $table->string('nom')->default('tirelire');
            $table->foreignId('user_id');
            $table->string('date_debut');
            $table->string('date_fin');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tirelires');
    }
};
