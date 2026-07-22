<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Editable standalone pages: عن المحامي، التراخيص، منهجية العمل،
     * سياسة الخصوصية، الشروط والأحكام.
     *
     * Each row carries a stable `key` so a controller and Blade template can
     * bind to a specific page, while the client still edits its copy and SEO.
     * The client can change the wording of سياسة الخصوصية; they cannot delete
     * it out from under the route.
     */
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();

            // Immutable identifier used by routes/templates (about, licenses,
            // methodology, privacy, terms). Not editable in the admin.
            $table->string('key')->unique();

            $table->string('slug')->unique();
            $table->string('title');
            $table->string('h1');
            $table->text('intro')->nullable();
            $table->longText('body')->nullable();

            // Extra template-specific structured content — e.g. the areas of
            // focus and methodology blocks on the lawyer profile.
            $table->json('sections')->nullable();

            $table->foreignId('og_image_id')->nullable()->constrained('media')->nullOnDelete();

            $table->string('seo_title')->nullable();
            $table->string('seo_description', 320)->nullable();
            $table->string('canonical_url')->nullable();
            $table->boolean('is_indexable')->default(true);

            $table->boolean('needs_review')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
