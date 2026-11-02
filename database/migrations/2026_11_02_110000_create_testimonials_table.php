<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('author_name', 80);           // « Samia B. » : prénom + initiale
            $table->string('author_role', 120)->nullable(); // « Maman de Yasmine, 3e année »
            $table->string('city', 80)->nullable();
            $table->string('country', 2)->nullable();
            $table->string('program', 20)->default('general'); // tunisia | france | qatar | saudi | quran | general
            $table->unsignedTinyInteger('rating')->default(5);
            $table->text('quote');
            $table->text('quote_ar')->nullable();
            $table->text('quote_en')->nullable();
            $table->string('source', 20)->default('admin'); // admin | site (formulaire « Laisser un avis »)
            $table->string('contact', 150)->nullable();   // e-mail / téléphone, jamais affiché
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->date('received_at')->nullable();
            $table->timestamps();

            $table->index(['is_published', 'program']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
