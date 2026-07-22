<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional contact-form submissions.
     *
     * The form is off by default and only appears when the client enables it
     * in settings. Phone and WhatsApp always remain reachable in one tap —
     * nobody is ever made to fill a qualification form to contact the office.
     */
    public function up(): void
    {
        Schema::create('contact_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('subject')->nullable();
            $table->text('message');

            // Attribution, so an enquiry can be traced to its campaign.
            $table->string('page_path', 512)->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('gclid')->nullable();
            $table->string('ip_hash', 64)->nullable();

            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_submissions');
    }
};
