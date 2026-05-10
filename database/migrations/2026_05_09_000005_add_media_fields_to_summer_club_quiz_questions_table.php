<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('summer_club_quiz_questions', function (Blueprint $table) {
            $table->string('media_type')->nullable()->after('question');
            $table->string('media_path')->nullable()->after('media_type');
            $table->string('media_url')->nullable()->after('media_path');
        });
    }

    public function down(): void
    {
        Schema::table('summer_club_quiz_questions', function (Blueprint $table) {
            $table->dropColumn([
                'media_type',
                'media_path',
                'media_url',
            ]);
        });
    }
};
