<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('summer_club_resources', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('is_locked');
            $table->integer('featured_sort_order')->default(0)->after('is_featured');

            $table->index(['is_published', 'is_featured', 'featured_sort_order'], 'summer_club_resources_featured_index');
        });
    }

    public function down(): void
    {
        Schema::table('summer_club_resources', function (Blueprint $table) {
            $table->dropIndex('summer_club_resources_featured_index');
            $table->dropColumn(['is_featured', 'featured_sort_order']);
        });
    }
};
