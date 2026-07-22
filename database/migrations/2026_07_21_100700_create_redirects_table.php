<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Legacy URL redirect map.
     *
     * The previous site is inactive but may still hold link equity, so old
     * paths get a server-side 301 to the closest genuinely relevant new page
     * — never a blanket redirect to the homepage. Paths with no meaningful
     * successor get a 410, which tells Google the URL is intentionally gone
     * rather than temporarily broken.
     */
    public function up(): void
    {
        Schema::create('redirects', function (Blueprint $table) {
            $table->id();

            // Stored normalised: leading slash, no host, no trailing slash,
            // decoded UTF-8. Matching happens on the decoded path so an
            // Arabic legacy URL matches whether or not the crawler encoded it.
            $table->string('from_path')->unique();

            // Null when status is 410 — a gone resource has no destination.
            $table->string('to_path')->nullable();

            $table->unsignedSmallInteger('status_code')->default(301);
            $table->boolean('is_active')->default(true)->index();

            // Lets the client see which legacy URLs still receive traffic and
            // which rules are dead weight.
            $table->unsignedBigInteger('hits')->default(0);
            $table->timestamp('last_hit_at')->nullable();

            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redirects');
    }
};
