<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contributions', function (Blueprint $table) {
            if (! Schema::hasColumn('contributions', 'statut')) {
                $table->string('statut')->default('succeeded');
            }
            if (! Schema::hasColumn('contributions', 'provider')) {
                $table->string('provider')->nullable();
            }
            if (! Schema::hasColumn('contributions', 'recu_path')) {
                $table->string('recu_path')->nullable();
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'preferred_payment_method')) {
                $table->string('preferred_payment_method')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('contributions', function (Blueprint $table) {
            foreach (['statut', 'provider', 'recu_path'] as $column) {
                if (Schema::hasColumn('contributions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'preferred_payment_method')) {
                $table->dropColumn('preferred_payment_method');
            }
        });
    }
};
