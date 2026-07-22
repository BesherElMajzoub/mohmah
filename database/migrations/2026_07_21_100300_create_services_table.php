<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Service pages.
     *
     * The body is split into named sections rather than one blob of HTML.
     * That is what stops the 25+ service pages from collapsing into the same
     * text with words swapped: each page must fill a distinct audience, scope
     * and process, and an empty section is visible in the admin as a gap.
     */
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_category_id')->constrained()->cascadeOnDelete();

            $table->string('slug')->unique();
            $table->string('title');

            // The single H1. Kept separate from `title` because the nav label
            // and the page heading are often not the same sentence.
            $table->string('h1');

            // One-line summary used on cards and in the mega menu.
            $table->string('summary')->nullable();

            $table->longText('overview')->nullable();

            // Structured sections, all nullable so a page can legitimately
            // omit one rather than pad it with filler.
            $table->json('audience')->nullable();      // ["...", "..."]
            $table->json('scope')->nullable();         // ["...", "..."]
            $table->json('process')->nullable();       // [{"title":"...","body":"..."}]
            $table->json('faqs')->nullable();          // [{"question":"...","answer":"..."}]

            // Which of the four verified licences is factually relevant here.
            // Keys reference config('site.licenses'); an empty array means the
            // page shows no licence block at all.
            $table->json('license_keys')->nullable();

            $table->text('disclaimer')->nullable();

            $table->foreignId('og_image_id')->nullable()->constrained('media')->nullOnDelete();

            // --- SEO ---
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 320)->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('focus_phrase')->nullable();
            $table->boolean('is_indexable')->default(true);

            // --- Editorial ---
            $table->string('status')->default('draft')->index(); // draft|published
            $table->timestamp('published_at')->nullable()->index();

            // Set by the seeded drafts. Surfaces an admin banner telling the
            // client this copy still needs professional legal review, and is
            // never rendered publicly.
            $table->boolean('needs_review')->default(false);

            $table->unsignedInteger('position')->default(0)->index();
            $table->timestamps();
        });

        // Self-referencing "related services" links, ordered for display.
        Schema::create('related_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('related_service_id')->constrained('services')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);

            $table->unique(['service_id', 'related_service_id'], 'related_services_pair_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('related_services');
        Schema::dropIfExists('services');
    }
};
