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

            if (! Schema::hasColumn('courses', 'delivery_type')) {
                $table->string('delivery_type', 30)->default('pdf')->after('description_en');
            }

            if (! Schema::hasColumn('courses', 'content_path')) {
                $table->string('content_path')->nullable()->after('delivery_type');
            }

            if (! Schema::hasColumn('courses', 'external_url')) {
                $table->string('external_url', 500)->nullable()->after('content_path');
            }

            if (! Schema::hasColumn('courses', 'access_note')) {
                $table->text('access_note')->nullable()->after('external_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $columns = [
                'title_en',
                'description_en',
                'delivery_type',
                'content_path',
                'external_url',
                'access_note',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('courses', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};