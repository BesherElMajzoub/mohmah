<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Support\Content;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Fixed pages: edit only.
 *
 * There is no create or delete. These five pages are bound to named routes,
 * and letting the client delete سياسة الخصوصية would take a live URL down.
 * The slug is editable, but the `key` is not.
 */
class PageController extends Controller
{
    public function index(): View
    {
        return view('admin.pages.index', [
            'pages' => Page::query()->orderBy('id')->get(),
        ]);
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.form', ['page' => $page]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'h1' => ['required', 'string', 'max:190'],
            'intro' => ['nullable', 'string', 'max:600'],
            'body' => ['nullable', 'string'],
            'og_image_id' => ['nullable', 'exists:media,id'],
            'seo_title' => ['nullable', 'string', 'max:70'],
            'seo_description' => ['nullable', 'string', 'max:160'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
            'is_indexable' => ['boolean'],
        ]);

        $data['is_indexable'] = $request->boolean('is_indexable');

        // Once the client has replaced the placeholder copy, the review flag
        // clears itself — one less thing for them to remember to untick.
        $data['needs_review'] = Content::needsConfirmation($data['body'] ?? '');

        $page->update($data);

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('status', 'تم حفظ الصفحة.');
    }
}
