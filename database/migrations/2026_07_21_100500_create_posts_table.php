<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Blog articles.
     *
     * `status` and `published_at` are separate on purpose: 'scheduled' means
     * the client has finished the article and chosen a future date, and the
     * public scope requires published_at <= now(). A row can therefore be
     * complete, approved, and still correctly absent from the sitemap.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_category_id')->constrained()->cascadeOnDelete();

            $table->string('slug')->unique();
            $table->string('title');
            $table->string('h1')->nullable();
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();

            $table->foreignId('cover_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('og_image_id')->nullable()->constrained('media')->nullOnDelete();

            // Free text rather than a users FK: the byline and the reviewing
            // lawyer are editorial facts about the article, not accounts in
            // this application.
            $table->string('author_name')->nullable();
            $table->string('reviewer_name')->nullable();

            // Cached word-count estimate, recalculated whenever body changes.
            $table->unsignedSmallInteger('reading_minutes')->nullable();

            // --- SEO ---
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 320)->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('focus_phrase')->nullable();
            $table->boolean('is_indexable')->default(true);

            // --- Editorial ---
            $table->string('status')->default('draft')->index(); // draft|scheduled|published
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('content_updated_at')->nullable();
            $table->boolean('needs_review')->default(false);

            $table->timestamps();

            // The public listing query: published rows, newest first.
            $table->index(['status', 'published_at']);
        });

        // Articles → services, so a service page can show genuinely relevant reading.
        Schema::create('post_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);

            $table->unique(['post_id', 'service_id']);
        });

        // Article → article links.
        Schema::create('related_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('related_post_id')->constrained('posts')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);

            $table->unique(['post_id', 'related_post_id'], 'related_posts_pair_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('related_posts');
        Schema::dropIfExists('post_service');
        Schema::dropIfExists('posts');
    }
};
