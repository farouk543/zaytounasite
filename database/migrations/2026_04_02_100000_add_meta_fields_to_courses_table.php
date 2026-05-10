<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('difficulty_level')->nullable()->after('sort_order');  // beginner | intermediate | advanced
            $table->unsignedSmallInteger('duration_minutes')->nullable()->after('difficulty_level');
            $table->boolean('is_featured')->default(false)->after('duration_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['difficulty_level', 'duration_minutes', 'is_featured']);
        });
    }
};
