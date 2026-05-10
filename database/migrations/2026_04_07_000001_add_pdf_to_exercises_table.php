<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            // interactive (questions/réponses) ou pdf (document uploadé)
            $table->string('exercise_type', 20)->default('interactive')->after('course_pack_item_id');
            $table->string('pdf_path')->nullable()->after('exercise_type');
        });
    }

    public function down(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->dropColumn(['exercise_type', 'pdf_path']);
        });
    }
};
