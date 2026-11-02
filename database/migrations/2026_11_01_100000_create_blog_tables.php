<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('name_en')->nullable();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_category_id')->nullable()->constrained('blog_categories')->nullOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('slug')->unique();

            $table->string('title');
            $table->string('title_ar')->nullable();
            $table->string('title_en')->nullable();

            $table->text('excerpt')->nullable();
            $table->text('excerpt_ar')->nullable();
            $table->text('excerpt_en')->nullable();

            $table->longText('content')->nullable();
            $table->longText('content_ar')->nullable();
            $table->longText('content_en')->nullable();

            $table->string('cover_image')->nullable();

            // SEO (Google) — si vide, on retombe sur le titre / l'extrait.
            $table->string('meta_title')->nullable();
            $table->string('meta_title_ar')->nullable();
            $table->string('meta_title_en')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('meta_description_ar', 320)->nullable();
            $table->string('meta_description_en', 320)->nullable();
            $table->string('focus_keyword')->nullable();
            // Marchés visés (codes pays de config/marketing.php) ; vide = tous les pays.
            $table->json('target_countries')->nullable();

            $table->string('status', 20)->default('draft'); // draft | published
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamps();

            $table->index(['status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('blog_categories');
    }
};
