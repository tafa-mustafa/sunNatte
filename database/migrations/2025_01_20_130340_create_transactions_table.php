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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
             $table->unsignedBigInteger('tirelire_id')->nullable(); // Nullable pour permettre d'autres types de transactions
            $table->string('session_id')->unique();
            $table->decimal('montant', 10, 2);
            $table->string('statut')->default('pending'); // pending, succeeded, failed
            $table->timestamps();
            $table->foreign('tirelire_id')->references('id')->on('tirelires')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
