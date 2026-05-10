<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_pack_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pack_id')
                ->constrained('courses')
                ->cascadeOnDelete();

            $table->string('item_type', 30);
            // course | series | quiz | exam_prep | resource

            $table->string('title');
            $table->string('title_ar')->nullable();
            $table->string('title_en')->nullable();

            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();

            $table->foreignId('linked_course_id')
                ->nullable()
                ->constrained('courses')
                ->nullOnDelete();

            $table->string('resource_path')->nullable();
            $table->string('external_url', 500)->nullable();

            $table->unsignedInteger('duration_minutes')->nullable();

            $table->boolean('is_required')->default(true);
            $table->boolean('is_preview')->default(false);

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['pack_id', 'sort_order']);
            $table->index(['pack_id', 'item_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_pack_items');
    }
};