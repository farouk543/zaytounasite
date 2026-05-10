<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_pack_item_progress', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('pack_id')
                ->constrained('courses')
                ->cascadeOnDelete();

            $table->foreignId('course_pack_item_id')
                ->constrained('course_pack_items')
                ->cascadeOnDelete();

            $table->boolean('is_started')->default(false);
            $table->boolean('is_completed')->default(false);

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();

            $table->unsignedInteger('progress_percent')->default(0);

            $table->timestamps();

            $table->unique(['user_id', 'course_pack_item_id']);
            $table->index(['user_id', 'pack_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_pack_item_progress');
    }
};