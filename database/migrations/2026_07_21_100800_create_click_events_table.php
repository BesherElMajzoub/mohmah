<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * First-party conversion log for call / WhatsApp / contact-form clicks.
     *
     * This exists alongside GA4, not instead of it. GA4 is blocked by a large
     * share of visitors and aggregates away the detail; this table is the
     * office's own record of which page and which campaign produced each
     * enquiry, and it survives analytics being reconfigured.
     *
     * Privacy: the raw IP is never stored. It is HMAC'd with the app key so
     * repeat visits can be recognised without the table holding a personal
     * identifier.
     */
    public function up(): void
    {
        Schema::create('click_events', function (Blueprint $table) {
            $table->id();

            // call | whatsapp | contact_form
            $table->string('type', 32)->index();

            // Where the click happened.
            $table->string('page_path', 512);
            $table->string('page_type', 64)->nullable();   // home, service, post, contact...
            $table->string('placement', 64)->nullable();   // header, hero, sticky_bar, footer...

            // --- Attribution, captured on landing and replayed on click ---
            $table->string('utm_source')->nullable()->index();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable()->index();
            $table->string('utm_term')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('gclid')->nullable()->index();
            $table->string('referrer', 512)->nullable();
            $table->string('landing_path', 512)->nullable();

            // --- Client ---
            $table->string('device', 16)->nullable()->index(); // mobile|tablet|desktop
            $table->string('user_agent', 512)->nullable();
            $table->string('ip_hash', 64)->nullable()->index();

            // Groups clicks from one browsing session without a login.
            $table->string('visitor_id', 64)->nullable()->index();

            $table->timestamps();

            // Backs the dashboard's "events of type X over date range" query.
            $table->index(['type', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('click_events');
    }
};
