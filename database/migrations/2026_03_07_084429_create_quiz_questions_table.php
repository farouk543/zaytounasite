<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('quiz_id')
                ->constrained('quizzes')
                ->cascadeOnDelete();

            $table->string('question_type', 30)->default('single_choice');
            // single_choice | multiple_choice | true_false | short_answer

            $table->text('question_text');
            $table->text('question_text_ar')->nullable();
            $table->text('question_text_en')->nullable();

            $table->text('explanation')->nullable();
            $table->text('explanation_ar')->nullable();
            $table->text('explanation_en')->nullable();

            $table->unsignedInteger('points')->default(1);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['quiz_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};