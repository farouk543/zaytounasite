<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Une ligne par page vue (visiteurs humains, hors admins). Aucune IP stockée :
        // visitor_hash = sha256(sel mensuel + IP + navigateur) → pas de cookie, anonyme.
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_hash', 64);
            $table->string('path', 500);
            $table->string('route_name', 120)->nullable();
            $table->string('title', 200)->nullable();
            $table->string('referrer_host', 190)->nullable();
            $table->string('source', 30)->default('direct');
            $table->string('utm_source', 100)->nullable();
            $table->string('utm_medium', 100)->nullable();
            $table->string('utm_campaign', 150)->nullable();
            $table->string('country', 2)->nullable();
            $table->string('device', 10)->nullable();   // desktop | mobile | tablet
            $table->string('browser', 30)->nullable();
            $table->string('os', 30)->nullable();
            $table->string('locale', 5)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
            $table->index(['path', 'created_at']);
            $table->index(['visitor_hash', 'created_at']);
        });

        Schema::create('click_events', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_hash', 64);
            $table->string('page_path', 500);
            $table->string('label', 150);
            $table->string('target', 500)->nullable();
            $table->string('kind', 20)->default('link'); // link | button | external | cta
            $table->string('device', 10)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
            $table->index(['page_path', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('click_events');
        Schema::dropIfExists('page_views');
    }
};
