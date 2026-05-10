<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercise_item_answers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('exercise_item_id')
                ->constrained('exercise_items')
                ->cascadeOnDelete();

            // Texte de l'élément / réponse / option
            $table->string('answer_text');
            $table->string('answer_text_ar')->nullable();
            $table->string('answer_text_en')->nullable();

            // Correspondance — côté droit pour type matching
            $table->string('match_text')->nullable();
            $table->string('match_text_ar')->nullable();
            $table->string('match_text_en')->nullable();

            // Image optionnelle (choix illustré)
            $table->string('image_path')->nullable();

            // Catégorie cible pour type categorization
            $table->string('category')->nullable();
            $table->string('category_ar')->nullable();
            $table->string('category_en')->nullable();

            // Pour single_choice, multiple_choice, true_false, fill_blank, short_answer, word_bank
            $table->boolean('is_correct')->default(false);

            // Pour calculation : valeur numérique attendue
            $table->decimal('correct_value', 15, 4)->nullable();

            // Pour calculation : tolérance ±  (ex: 0.5 accepte 9.5–10.5 pour valeur 10)
            $table->decimal('tolerance', 10, 4)->default(0);

            // Pour ordering : position correcte (1, 2, 3…)
            // Pour les autres types : ordre d'affichage
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['exercise_item_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercise_item_answers');
    }
};
