<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostCategory;
use App\Support\ArabicSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PostCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.post-categories.index', [
            'categories' => PostCategory::query()
                ->withCount('posts')
                ->orderBy('position')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.post-categories.form', ['category' => new PostCategory]);
    }

    public function store(Request $request): RedirectResponse
    {
        PostCategory::create($this->validated($request));

        return redirect()
            ->route('admin.post-categories.index')
            ->with('status', 'تم إنشاء القسم.');
    }

    public function edit(PostCategory $postCategory): View
    {
        return view('admin.post-categories.form', ['category' => $postCategory]);
    }

    public function update(Request $request, PostCategory $postCategory): RedirectResponse
    {
        $postCategory->update($this->validated($request, $postCategory->id));

        return redirect()
            ->route('admin.post-categories.index')
            ->with('status', 'تم حفظ التعديلات.');
    }

    public function destroy(PostCategory $postCategory): RedirectResponse
    {
        if ($postCategory->posts()->exists()) {
            return back()->withErrors([
                'category' => 'لا يمكن حذف قسم يحتوي على مقالات. انقل المقالات إلى قسم آخر أولاً.',
            ]);
        }

        $postCategory->delete();

        return redirect()
            ->route('admin.post-categories.index')
            ->with('status', 'تم حذف القسم.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', Rule::unique('post_categories', 'slug')->ignore($ignoreId)],
            'intro' => ['nullable', 'string', 'max:500'],
            'seo_title' => ['nullable', 'string', 'max:70'],
            'seo_description' => ['nullable', 'string', 'max:160'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['slug'] = ArabicSlug::make(filled($data['slug'] ?? null) ? $data['slug'] : $data['title']);
        $data['position'] ??= 0;

        return $data;
    }
}
