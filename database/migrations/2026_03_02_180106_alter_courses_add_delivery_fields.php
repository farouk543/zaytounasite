<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (! Schema::hasColumn('courses', 'delivery_type')) {
                $table->string('delivery_type')->default('pdf');
            }
            if (! Schema::hasColumn('courses', 'content_path')) {
                $table->string('content_path')->nullable();
            }
            if (! Schema::hasColumn('courses', 'external_url')) {
                $table->string('external_url')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['delivery_type', 'content_path', 'external_url']);
        });
    }
};