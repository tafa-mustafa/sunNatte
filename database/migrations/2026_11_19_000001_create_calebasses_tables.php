<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calebasses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('createur_id');
            $table->string('nom');
            $table->text('description')->nullable();
            $table->string('type')->default('projet'); // projet, fete, urgence, epargne
            $table->decimal('objectif', 12, 2)->nullable();
            $table->date('date_echeance')->nullable();
            $table->string('statut')->default('open'); // open, closed, archived
            $table->string('code_invitation', 8)->unique();
            $table->timestamps();

            $table->foreign('createur_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('calebasse_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('calebasse_id');
            $table->unsignedBigInteger('user_id');
            $table->string('badge')->default('membre'); // createur, membre
            $table->timestamps();

            $table->unique(['calebasse_id', 'user_id']);
            $table->foreign('calebasse_id')->references('id')->on('calebasses')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('calebasse_cotisations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('calebasse_id');
            $table->unsignedBigInteger('user_id');
            $table->decimal('montant', 12, 2);
            $table->string('statut')->default('pending'); // pending, succeeded, failed
            $table->string('session_id')->nullable()->unique();
            $table->string('provider')->default('wave');
            $table->timestamps();

            $table->foreign('calebasse_id')->references('id')->on('calebasses')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calebasse_cotisations');
        Schema::dropIfExists('calebasse_user');
        Schema::dropIfExists('calebasses');
    }
};
