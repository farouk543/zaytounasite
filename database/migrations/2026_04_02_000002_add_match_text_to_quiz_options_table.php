<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_options', function (Blueprint $table) {
            // Right-side text for matching questions (left = option_text, right = match_text)
            $table->string('match_text')->nullable()->after('option_text_en')
                ->comment('Right side of a matching pair');
            $table->string('match_text_ar')->nullable()->after('match_text');
            $table->string('match_text_en')->nullable()->after('match_text_ar');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_options', function (Blueprint $table) {
            $table->dropColumn(['match_text', 'match_text_ar', 'match_text_en']);
        });
    }
};
