<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();

            // ✅ hiérarchie B : Course -> Subject -> Level -> Track
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();

            $table->string('slug');
            $table->string('title');
            $table->string('title_ar')->nullable();

            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();

            $table->boolean('is_paid')->default(true);
            $table->unsignedInteger('price_cents')->default(0);
            $table->string('currency', 3)->default('TND');

            $table->string('thumbnail_path')->nullable();
            $table->boolean('is_published')->default(false);

            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['subject_id', 'slug']);
            $table->index(['subject_id', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};