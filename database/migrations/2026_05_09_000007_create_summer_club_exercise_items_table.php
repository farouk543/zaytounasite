<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('summer_club_exercise_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('summer_club_exercise_id')
                ->constrained('summer_club_exercises')
                ->cascadeOnDelete();
            $table->string('type');
            $table->text('instruction');
            $table->text('question')->nullable();
            $table->string('media_type')->nullable();
            $table->string('media_path')->nullable();
            $table->string('media_url')->nullable();
            $table->json('options')->nullable();
            $table->json('correct_answer')->nullable();
            $table->text('explanation')->nullable();
            $table->integer('points')->default(1);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('type');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('summer_club_exercise_items');
    }
};
