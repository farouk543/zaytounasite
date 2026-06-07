<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('summer_club_quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('summer_club_quiz_id')->constrained('summer_club_quizzes')->cascadeOnDelete();
            $table->foreignId('summer_club_enrollment_id')->nullable()->constrained('summer_club_enrollments')->nullOnDelete();
            $table->json('answers')->nullable();
            $table->integer('score')->default(0);
            $table->integer('total')->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->boolean('passed')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('summer_club_quiz_id');
            $table->index('summer_club_enrollment_id');
            $table->index('completed_at');
            $table->index(['user_id', 'summer_club_enrollment_id', 'summer_club_quiz_id'], 'sc_quiz_attempts_user_enrollment_quiz_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('summer_club_quiz_attempts');
    }
};
