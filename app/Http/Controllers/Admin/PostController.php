<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PostRequest;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Article management.
 *
 * Publishing here is the entire workflow: saving fires SitemapCacheObserver,
 * so a new article is in /sitemap.xml immediately with no second step.
 */
class PostController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.posts.index', [
            'posts' => Post::query()
                ->with('category')
                ->when($request->string('q')->trim()->value(), fn ($q, $term) => $q->where('title', 'like', "%{$term}%"))
                ->when($request->string('status')->value(), fn ($q, $status) => $q->where('status', $status))
                ->when($request->integer('category'), fn ($q, $id) => $q->where('post_category_id', $id))
                ->latest('created_at')
                ->paginate(25)
                ->withQueryString(),
            'categories' => PostCategory::query()->orderBy('position')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.posts.form', [
            'post' => new Post([
                'status' => Post::STATUS_DRAFT,
                'is_indexable' => true,
                // Articles are published under the lawyer's identity by
                // default — that is who the reader is trusting.
                'author_name' => config('site.lawyer_name'),
            ]),
            ...$this->formOptions(),
        ]);
    }

    public function store(PostRequest $request): RedirectResponse
    {
        $post = Post::create($request->postData());

        $this->afterSave($post, $request);

        return redirect()
            ->route('admin.posts.edit', $post)
            ->with('status', 'تم إنشاء المقال.');
    }

    public function edit(Post $post): View
    {
        return view('admin.posts.form', [
            'post' => $post->load(['services', 'relatedPosts', 'coverImage']),
            ...$this->formOptions(),
        ]);
    }

    public function update(PostRequest $request, Post $post): RedirectResponse
    {
        $post->update($request->postData());

        $this->afterSave($post, $request);

        return redirect()
            ->route('admin.posts.edit', $post)
            ->with('status', 'تم حفظ التعديلات. المقال محدَّث في خريطة الموقع.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()
            ->route('admin.posts.index')
            ->with('status', 'تم حذف المقال.');
    }

    private function afterSave(Post $post, PostRequest $request): void
    {
        // Recomputed on every save so the reading estimate always matches the
        // body the visitor is about to read.
        $post->updateQuietly(['reading_minutes' => $post->calculateReadingMinutes()]);

        $services = collect($request->validated('service_ids', []))
            ->values()
            ->mapWithKeys(fn ($id, $i) => [$id => ['position' => $i]]);

        $post->services()->sync($services);

        $related = collect($request->validated('related_post_ids', []))
            ->reject(fn ($id) => (int) $id === $post->id)
            ->values()
            ->mapWithKeys(fn ($id, $i) => [$id => ['position' => $i]]);

        $post->relatedPosts()->sync($related);
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'categories' => PostCategory::query()->orderBy('position')->get(),
            'allServices' => Service::query()->orderBy('title')->get(['id', 'title']),
            'allPosts' => Post::query()->orderBy('title')->get(['id', 'title']),
        ];
    }
}
