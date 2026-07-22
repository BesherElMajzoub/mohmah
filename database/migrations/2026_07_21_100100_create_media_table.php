<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A deliberately small media table.
     *
     * The site needs uploads with Arabic alt text and intrinsic dimensions
     * (to reserve layout space and avoid CLS). That is the whole requirement,
     * so it does not justify a media-library package and its conversions,
     * queues and polymorphic collections.
     */
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');

            // Null for SVG and other formats without intrinsic raster dimensions.
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();

            // Arabic alt text. Nullable in the schema so an upload is never
            // blocked, but the admin flags any image missing it.
            $table->string('alt_ar')->nullable();
            $table->string('caption_ar')->nullable();

            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
