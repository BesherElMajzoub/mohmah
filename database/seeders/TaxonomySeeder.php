<?php

namespace Database\Seeders;

use App\Models\PostCategory;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

/**
 * The service and editorial taxonomies.
 *
 * These are structural facts about what the office does — supplied by the
 * client — so they belong in a seeder. Nothing here is invented: the four
 * pillars and the four blog sections are exactly those specified.
 *
 * updateOrCreate on the slug makes this safe to re-run without duplicating.
 */
class TaxonomySeeder extends Seeder
{
    public function run(): void
    {
        $serviceCategories = [
            [
                'slug' => 'محاماة-الأعمال',
                'title' => 'محاماة الأعمال',
                'menu_label' => 'خدمات الأعمال',
                'intro' => 'تمثيل الشركات في نزاعاتها التجارية والعمالية، وصياغة عقودها، وضبط حوكمتها وامتثالها.',
                'position' => 1,
            ],
            [
                'slug' => 'التحكيم',
                'title' => 'التحكيم',
                'menu_label' => 'التحكيم',
                'intro' => 'تمثيل الأطراف وإدارة الإجراءات وصياغة اتفاقيات التحكيم وتنفيذ الأحكام.',
                'position' => 2,
            ],
            [
                'slug' => 'التوثيق',
                'title' => 'التوثيق',
                'menu_label' => 'التوثيق',
                'intro' => 'توثيق الوكالات وعقود الشركات والإقرارات والتصرفات، وخدمات الورثة والتوثيق العقاري.',
                'position' => 3,
            ],
            [
                'slug' => 'الخدمات-العقارية',
                'title' => 'الخدمات العقارية',
                'menu_label' => 'العقارات',
                'intro' => 'التسجيل العيني للعقار ونقل الملكية والاستشارات القانونية العقارية.',
                'position' => 4,
            ],
        ];

        foreach ($serviceCategories as $category) {
            ServiceCategory::updateOrCreate(['slug' => $category['slug']], $category);
        }

        $postCategories = [
            [
                'slug' => 'الشركات-والعقود',
                'title' => 'الشركات والعقود',
                'intro' => 'تحليلات موجّهة لأصحاب الشركات ومديريها حول تأسيس الكيانات وصياغة العقود وإدارة المخاطر التعاقدية.',
                'position' => 1,
            ],
            [
                'slug' => 'التحكيم',
                'title' => 'التحكيم',
                'intro' => 'مقالات في شرط التحكيم وإجراءاته وتنفيذ أحكامه، موجّهة لمن يتعاقد أو يتنازع تجارياً.',
                'position' => 2,
            ],
            [
                'slug' => 'التوثيق-والتركات',
                'title' => 'التوثيق والتركات',
                'intro' => 'الوكالات والإقرارات وقسمة التركات وما يتصل بها من إجراءات توثيقية.',
                'position' => 3,
            ],
            [
                'slug' => 'العقارات',
                'title' => 'العقارات',
                'intro' => 'التسجيل العيني ونقل الملكية والمعاملات العقارية من زاوية المخاطر القانونية.',
                'position' => 4,
            ],
        ];

        foreach ($postCategories as $category) {
            PostCategory::updateOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
