<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('enrollments', function (\Illuminate\Database\Schema\Blueprint $table) {
            if (! Schema::hasColumn('enrollments', 'access_ends_at')) {
                $table->timestamp('access_ends_at')->nullable()->after('enrolled_at');
            }
            if (! Schema::hasColumn('enrollments', 'status')) {
                $table->string('status')->default('active')->after('access_ends_at');
            }
        });

        // Index séparé avec vérification d'existence
        $indexName = 'enrollments_user_id_course_id_status_index';
        $indexes = match (DB::connection()->getDriverName()) {
            'sqlite' => collect(DB::select("PRAGMA index_list('enrollments')"))
                ->pluck('name')
                ->toArray(),
            default => collect(DB::select('SHOW INDEX FROM enrollments'))
                ->pluck('Key_name')
                ->toArray(),
        };

        if (! in_array($indexName, $indexes, true)) {
            Schema::table('enrollments', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->index(['user_id', 'course_id', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'course_id', 'status']);
            $table->dropColumn(['access_ends_at', 'status']);
        });
    }
};
