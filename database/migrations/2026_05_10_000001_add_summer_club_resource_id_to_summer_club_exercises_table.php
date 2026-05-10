<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('summer_club_exercises', function (Blueprint $table) {
            $table->foreignId('summer_club_resource_id')
                ->nullable()
                ->after('id')
                ->constrained('summer_club_resources')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('summer_club_exercises', function (Blueprint $table) {
            $table->dropConstrainedForeignId('summer_club_resource_id');
        });
    }
};
