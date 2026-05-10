<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_pack_items', function (Blueprint $table) {
            $table->json('resource_paths')->nullable()->after('resource_path')
                ->comment('Multiple PDF paths for a pack item');
        });
    }

    public function down(): void
    {
        Schema::table('course_pack_items', function (Blueprint $table) {
            $table->dropColumn('resource_paths');
        });
    }
};
