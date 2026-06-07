<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('summer_club_resources', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type');
            $table->string('subject')->nullable();
            $table->string('level')->nullable();
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->string('file_path')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_locked')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_published', 'sort_order']);
            $table->index('type');
            $table->index('subject');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('summer_club_resources');
    }
};
