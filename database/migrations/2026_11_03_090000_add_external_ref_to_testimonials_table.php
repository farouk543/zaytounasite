<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Référence d'un avis importé de la plateforme d'enseignement (« platform:<id> ») : évite les doublons. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->string('external_ref', 40)->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropUnique(['external_ref']);
            $table->dropColumn('external_ref');
        });
    }
};
