<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostCategory;
use App\Support\Seo\SeoData;
use Illuminate\View\View;

class PostController extends Controller
{
    private const PER_PAGE = 9;

    public function index(): View
    {
        $breadcrumbs = [
            ['title' => 'الرئيسية', 'path' => '/'],
            ['title' => 'المدونة', 'path' => null],
        ];

        $seo = (new SeoData(
            title: 'المدونة',
            description: 'مقالات تحليلية موجّهة لأصحاب الشركات والمستثمرين وملّاك العقارات في الشركات والعقود والتحكيم التجاري والتوثيق والتركات والخدمات العقارية.',
            // Paginated pages beyond the first are noindex: page 2 of a
            // listing has no search intent of its own and would compete with
            // the articles it links to.
            indexable: request()->integer('page', 1) <= 1,
        ))->withBreadcrumbs($breadcrumbs);

        return view('pages.posts.index', [
            'seo' => $seo,
            'breadcrumbs' => $breadcrumbs,
            'categories' => PostCategory::query()->orderBy('position')->get(),
            'posts' => Post::query()
                ->published()
                ->with('category')
                ->latest('published_at')
                ->paginate(self::PER_PAGE),
            'activeCategory' => null,
        ]);
    }

    /**
     * Resolve /المدونة/{slug} to either a category listing or an article.
     *
     * Categories are checked first: their slugs are few, curated, and the
     * admin prevents a duplicate within each table. A slug that matches
     * neither is a genuine 404.
     */
    public function entry(string $slug): View
    {
        $category = PostCategory::query()->where('slug', $slug)->first();

        if ($category) {
            return $this->category($category);
        }

        $post = Post::query()->where('slug', $slug)->firstOrFail();

        return $this->show($post);
    }

    public function category(PostCategory $category): View
    {
        $breadcrumbs = [
            ['title' => 'الرئيسية', 'path' => '/'],
            ['title' => 'المدونة', 'path' => '/المدونة'],
            ['title' => $category->title, 'path' => null],
        ];

        $seo = (new SeoData(
            title: $category->metaTitle(),
            description: $category->metaDescription(),
            canonical: $category->canonicalUrl(),
            indexable: $category->isIndexable() && request()->integer('page', 1) <= 1,
        ))->withBreadcrumbs($breadcrumbs);

        return view('pages.posts.index', [
            'seo' => $seo,
            'breadcrumbs' => $breadcrumbs,
            'categories' => PostCategory::query()->orderBy('position')->get(),
            'posts' => $category->posts()
                ->published()
                ->with('category')
                ->paginate(self::PER_PAGE),
            'activeCategory' => $category,
        ]);
    }

    public function show(Post $post): View
    {
        abort_unless($post->isPublished(), 404);

        $post->load([
            'category',
            'coverImage',
            'services' => fn ($q) => $q->published(),
            'relatedPosts' => fn ($q) => $q->published(),
        ]);

        $breadcrumbs = [
            ['title' => 'الرئيسية', 'path' => '/'],
            ['title' => 'المدونة', 'path' => '/المدونة'],
            ['title' => $post->category->title, 'path' => $post->category->path()],
            ['title' => $post->title, 'path' => null],
        ];

        $seo = (new SeoData(
            title: $post->metaTitle(),
            description: $post->metaDescription(),
            canonical: $post->canonicalUrl(),
            image: $post->ogImage ?? $post->coverImage,
            indexable: (bool) $post->is_indexable,
            type: 'article',
            publishedTime: $post->published_at?->toIso8601String(),
            modifiedTime: ($post->content_updated_at ?? $post->updated_at)?->toIso8601String(),
        ))->withBreadcrumbs($breadcrumbs);

        return view('pages.posts.show', [
            'seo' => $seo,
            'post' => $post,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
