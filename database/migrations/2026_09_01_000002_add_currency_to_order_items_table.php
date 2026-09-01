<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'currency')) {
                $table->string('currency', 3)->nullable()->after('unit_price_cents');
            }

            if (! Schema::hasColumn('order_items', 'base_price_cents')) {
                $table->integer('base_price_cents')->nullable()->after('currency');
            }

            if (! Schema::hasColumn('order_items', 'base_currency')) {
                $table->string('base_currency', 3)->nullable()->after('base_price_cents');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            foreach (['currency', 'base_price_cents', 'base_currency'] as $column) {
                if (Schema::hasColumn('order_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
