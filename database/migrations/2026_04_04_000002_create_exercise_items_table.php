<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercise_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('exercise_id')
                ->constrained('exercises')
                ->cascadeOnDelete();

            /*
             * Types disponibles :
             *   single_choice    — QCM choix unique
             *   multiple_choice  — QCM choix multiple
             *   true_false       — Vrai / Faux
             *   matching         — Relier par une flèche
             *   ordering         — Remettre dans l'ordre
             *   fill_blank       — Texte à trous  (___ dans l'énoncé)
             *   word_bank        — Banque de mots (glisser-déposer)
             *   categorization   — Classer par catégorie
             *   short_answer     — Réponse courte (texte libre corrigé)
             *   calculation      — Calcul numérique (valeur exacte ou plage)
             */
            $table->string('item_type', 30)->default('single_choice');

            $table->text('statement');
            $table->text('statement_ar')->nullable();
            $table->text('statement_en')->nullable();

            // Image optionnelle illustrant l'énoncé
            $table->string('image_path')->nullable();

            // Indice affiché à la demande
            $table->text('hint')->nullable();
            $table->text('hint_ar')->nullable();
            $table->text('hint_en')->nullable();

            // Correction / explication affichée après réponse
            $table->text('explanation')->nullable();
            $table->text('explanation_ar')->nullable();
            $table->text('explanation_en')->nullable();

            $table->unsignedInteger('points')->default(1);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['exercise_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercise_items');
    }
};
