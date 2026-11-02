<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Objectifs marketing mensuels, comparés aux statistiques réelles dans le back-office.
        Schema::create('marketing_goals', function (Blueprint $table) {
            $table->id();
            $table->string('month', 7);              // AAAA-MM
            $table->string('metric', 40);            // voir MarketingGoal::METRICS
            $table->string('country', 2)->nullable(); // null = tous pays confondus
            $table->decimal('target', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['month', 'metric', 'country']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_goals');
    }
};
