<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('summer_club_enrollments', function (Blueprint $table) {
            $table->string('pack_key')->nullable()->after('pack_name');
            $table->json('selected_subjects')->nullable()->after('pack_key');

            $table->index('pack_key');
        });
    }

    public function down(): void
    {
        Schema::table('summer_club_enrollments', function (Blueprint $table) {
            $table->dropIndex(['pack_key']);
            $table->dropColumn(['pack_key', 'selected_subjects']);
        });
    }
};
