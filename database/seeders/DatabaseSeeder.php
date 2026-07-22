<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeds only verified content.
 *
 * There are deliberately no demo articles, sample testimonials, placeholder
 * statistics or fake team members anywhere in these seeders. Everything here
 * is either a structural fact about the office (its service taxonomy, its
 * licence numbers) or copy drafted from the supplied scope and flagged for
 * the lawyer's review.
 *
 * The admin account is created separately by `php artisan make:admin`, so no
 * default credentials are ever committed or seeded.
 *
 * Note WithoutModelEvents is NOT used: the sitemap cache observer should fire
 * so a seeded environment has a correct sitemap immediately.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SettingsSeeder::class,
            TaxonomySeeder::class,
            ServiceSeeder::class,
            PageSeeder::class,
            LawFirmInitialBlogPostsSeeder::class,
        ]);
    }
}
