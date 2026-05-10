<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_id')
                ->nullable()
                ->constrained('courses')
                ->nullOnDelete();

            $table->foreignId('course_pack_item_id')
                ->nullable()
                ->constrained('course_pack_items')
                ->cascadeOnDelete();

            $table->string('title');
            $table->string('title_ar')->nullable();
            $table->string('title_en')->nullable();

            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();

            $table->unsignedInteger('passing_score')->default(50);
            $table->unsignedInteger('time_limit_minutes')->nullable();
            $table->unsignedInteger('max_attempts')->nullable();

            $table->boolean('is_randomized')->default(false);
            $table->boolean('show_result_immediately')->default(true);
            $table->boolean('is_published')->default(false);

            $table->timestamps();

            $table->index('course_id');
            $table->index('course_pack_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};