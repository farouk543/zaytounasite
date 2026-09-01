<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('summer_club_subscription_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('summer_club_subscription_requests', 'currency')) {
                $table->string('currency', 3)->default('TND')->after('price');
            }

            if (! Schema::hasColumn('summer_club_subscription_requests', 'base_price')) {
                $table->decimal('base_price', 10, 2)->nullable()->after('currency');
            }
        });
    }

    public function down(): void
    {
        Schema::table('summer_club_subscription_requests', function (Blueprint $table) {
            foreach (['currency', 'base_price'] as $column) {
                if (Schema::hasColumn('summer_club_subscription_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
