<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (! Schema::hasColumn('courses', 'title_en')) {
                $table->string('title_en')->nullable()->after('title_ar');
            }
            if (! Schema::hasColumn('courses', 'description_en')) {
                $table->text('description_en')->nullable()->after('description_ar');
            }
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['title_en', 'description_en']);
        });
    }
};