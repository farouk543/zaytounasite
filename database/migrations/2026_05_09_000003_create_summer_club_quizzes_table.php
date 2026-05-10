<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('summer_club_quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('summer_club_resource_id')
                ->nullable()
                ->constrained('summer_club_resources')
                ->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('subject')->nullable();
            $table->string('level')->nullable();
            $table->boolean('is_published')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_published', 'sort_order']);
            $table->index('subject');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('summer_club_quizzes');
    }
};
