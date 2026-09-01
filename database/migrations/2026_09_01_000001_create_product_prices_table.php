<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_prices')) {
            return;
        }

        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->morphs('priceable');
            $table->string('currency', 3);
            $table->integer('price_cents');
            $table->timestamps();

            $table->unique(['priceable_type', 'priceable_id', 'currency'], 'product_prices_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_prices');
    }
};
