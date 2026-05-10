<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();

            $table->unsignedInteger('unit_price_cents')->default(0);
            $table->unsignedInteger('qty')->default(1);

            $table->timestamps();

            $table->unique(['order_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};