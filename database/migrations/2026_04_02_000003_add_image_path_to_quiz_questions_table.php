<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('sort_order')
                ->comment('Optional illustrative image displayed above the question text');
        });

        Schema::table('course_pack_items', function (Blueprint $table) {
            $table->string('video_url')->nullable()->after('external_url')
                ->comment('YouTube or Vimeo embed URL for video content');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });

        Schema::table('course_pack_items', function (Blueprint $table) {
            $table->dropColumn('video_url');
        });
    }
};
