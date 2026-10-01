<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adhesions', function (Blueprint $table) {
            if (! Schema::hasColumn('adhesions', 'statut')) {
                $table->string('statut')->default('approved');
            }
            if (! Schema::hasColumn('adhesions', 'motif_rejet')) {
                $table->text('motif_rejet')->nullable();
            }
            if (! Schema::hasColumn('adhesions', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable();
            }
            if (! Schema::hasColumn('adhesions', 'reviewed_by')) {
                $table->unsignedBigInteger('reviewed_by')->nullable();
            }
        });

        if (Schema::hasTable('adhesions')) {
            DB::table('adhesions')->whereNull('statut')->update(['statut' => 'approved']);
        }

        Schema::table('documents', function (Blueprint $table) {
            if (! Schema::hasColumn('documents', 'adhesion_id')) {
                $table->unsignedBigInteger('adhesion_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('adhesions', function (Blueprint $table) {
            foreach (['statut', 'motif_rejet', 'reviewed_at', 'reviewed_by'] as $column) {
                if (Schema::hasColumn('adhesions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('documents', function (Blueprint $table) {
            if (Schema::hasColumn('documents', 'adhesion_id')) {
                $table->dropColumn('adhesion_id');
            }
        });
    }
};
