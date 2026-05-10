<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exercise_item_answers', function (Blueprint $table) {
            /*
             * blank_index (fill_blank, word_bank)
             * -------------------------------------------------
             * Pour fill_blank : chaque réponse correspond au N-ième ___ dans l'énoncé.
             *   blank_index = 0 → premier ___, 1 → deuxième ___, etc.
             * Pour word_bank : les mots corrects (is_correct = true) doivent indiquer
             *   quel blank ils remplissent. Les mots distracteurs laissent blank_index null.
             */
            $table->unsignedSmallInteger('blank_index')->nullable()->after('sort_order')
                ->comment('Index du blanc cible (0-based) pour fill_blank et word_bank');

            /*
             * match_mode (short_answer)
             * -------------------------------------------------
             * exact        → la réponse de l'étudiant doit être identique (insensible à la casse)
             * contains     → la réponse doit contenir cette chaîne
             * starts_with  → la réponse doit commencer par cette chaîne
             */
            $table->string('match_mode', 20)->nullable()->after('blank_index')
                ->comment('Mode de correspondance pour short_answer : exact | contains | starts_with');
        });
    }

    public function down(): void
    {
        Schema::table('exercise_item_answers', function (Blueprint $table) {
            $table->dropColumn(['blank_index', 'match_mode']);
        });
    }
};
