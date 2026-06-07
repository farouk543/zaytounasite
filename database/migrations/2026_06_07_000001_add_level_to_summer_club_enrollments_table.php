<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('summer_club_enrollments', function (Blueprint $table) {
            $table->string('level')->nullable()->after('selected_subjects')->index();
        });
    }

    public function down(): void
    {
        Schema::table('summer_club_enrollments', function (Blueprint $table) {
            $table->dropIndex(['level']);
            $table->dropColumn('level');
        });
    }
};
