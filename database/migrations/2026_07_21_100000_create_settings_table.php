<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Global site settings as a typed key/value store.
     *
     * Deliberately schemaless: the client will add contact details, hours and
     * social links over time, and a key/value table lets the admin surface a
     * curated form without a migration per field. Reads go through
     * App\Support\Settings, which caches the whole table as one array.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();

            // Drives casting on read: string, text, boolean, integer, json.
            $table->string('type')->default('string');

            // Groups the key into an admin tab (contact, branding, analytics...).
            $table->string('group')->default('general')->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
