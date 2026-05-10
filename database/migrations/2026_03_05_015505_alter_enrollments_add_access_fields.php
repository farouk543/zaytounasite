<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $cols = Schema::getColumnListing('enrollments');

            if (!in_array('status', $cols, true)) {
                $table->string('status')->default('active');
            }

            if (!in_array('access_ends_at', $cols, true)) {
                $table->timestamp('access_ends_at')->nullable();
            }
        });

        // index séparé (sqlite ok)
        Schema::table('enrollments', function (Blueprint $table) {
            // si l’index existe déjà, SQLite peut throw.
            // On le laisse optionnel: crée-le seulement si tu es sûr qu'il n'existe pas.
            // $table->index(['user_id', 'course_id', 'status']);
        });
    }

    public function down(): void
    {
        // On ne drop pas en down (safe) car sqlite + prod.
    }
};