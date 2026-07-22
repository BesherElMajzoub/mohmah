<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The four practice pillars: محاماة الأعمال، التحكيم، التوثيق، الخدمات العقارية.
     *
     * Slugs are stored as raw UTF-8 Arabic. MySQL matches them natively; the
     * encoding work happens on URL generation (App\Support\Url).
     */
    public function up(): void
    {
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');

            // Short label for the mega-menu column heading, where the full
            // title is often too long to sit comfortably.
            $table->string('menu_label')->nullable();

            $table->text('intro')->nullable();
            $table->unsignedInteger('position')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_categories');
    }
};
