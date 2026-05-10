<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_section_id')->nullable()->constrained('course_sections')->nullOnDelete();

            $table->string('title');
            $table->string('title_ar')->nullable();

            $table->enum('type', ['video', 'pdf', 'text', 'quiz'])->default('video');

            // pour V1: url externe (youtube/vimeo) OU path local (storage)
            $table->string('content_url')->nullable();
            $table->string('content_path')->nullable();

            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_free_preview')->default(false);

            $table->timestamps();

            $table->index(['course_id', 'course_section_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};