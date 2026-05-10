<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            // Remplacer unique(level_id, slug) → unique(branch_id, slug)
            // Compatible MySQL (pas besoin de recréer la table)
            $table->dropUnique(['level_id', 'slug']);
            $table->unique(['branch_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            // Gère les deux noms possibles (migration ancienne SQLite vs nouvelle MySQL)
            $indexes = collect(Schema::getIndexes('subjects'))->pluck('name');
            $toRemove = $indexes->first(fn ($n) => str_contains($n, 'branch_id_slug_unique'));
            if ($toRemove) {
                $table->dropIndex($toRemove);
            }
            $table->unique(['level_id', 'slug']);
        });
    }
};