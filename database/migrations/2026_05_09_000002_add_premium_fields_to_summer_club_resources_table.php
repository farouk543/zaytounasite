<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('summer_club_resources', function (Blueprint $table) {
            $table->string('cover_image_path')->nullable()->after('file_path');
            $table->longText('correction_content')->nullable()->after('cover_image_path');
            $table->string('correction_file_path')->nullable()->after('correction_content');
        });
    }

    public function down(): void
    {
        Schema::table('summer_club_resources', function (Blueprint $table) {
            $table->dropColumn([
                'cover_image_path',
                'correction_content',
                'correction_file_path',
            ]);
        });
    }
};
