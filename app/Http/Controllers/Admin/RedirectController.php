<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Redirect as RedirectModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * The legacy redirect map.
 *
 * The `hits` column is the useful part of this screen: after launch it shows
 * which old URLs are actually still being requested, which confirms the map
 * is working and reveals paths that were missed.
 */
class RedirectController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.redirects.index', [
            'redirects' => RedirectModel::query()
                ->when($request->string('q')->trim()->value(), fn ($q, $term) => $q
                    ->where('from_path', 'like', "%{$term}%")
                    ->orWhere('to_path', 'like', "%{$term}%"))
                ->orderByDesc('hits')
                ->orderBy('from_path')
                ->paginate(50)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.redirects.form', ['redirect' => new RedirectModel(['status_code' => 301, 'is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        RedirectModel::create($this->validated($request));

        return redirect()
            ->route('admin.redirects.index')
            ->with('status', 'تمت إضافة التحويل.');
    }

    public function edit(RedirectModel $redirect): View
    {
        return view('admin.redirects.form', ['redirect' => $redirect]);
    }

    public function update(Request $request, RedirectModel $redirect): RedirectResponse
    {
        $redirect->update($this->validated($request, $redirect->id));

        return redirect()
            ->route('admin.redirects.index')
            ->with('status', 'تم حفظ التحويل.');
    }

    public function destroy(RedirectModel $redirect): RedirectResponse
    {
        $redirect->delete();

        return redirect()
            ->route('admin.redirects.index')
            ->with('status', 'تم حذف التحويل.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'from_path' => ['required', 'string', 'max:255', Rule::unique('redirects', 'from_path')->ignore($ignoreId)],
            // Required unless the rule is a 410, where "gone" means there is
            // no destination by definition.
            'to_path' => ['nullable', 'string', 'max:255', Rule::requiredIf($request->integer('status_code') !== 410)],
            'status_code' => ['required', Rule::in([301, 302, 410])],
            'is_active' => ['boolean'],
            'note' => ['nullable', 'string', 'max:255'],
        ], [
            'to_path.required' => 'حدّد الوجهة، أو اختر الرمز 410 إذا كانت الصفحة محذوفة نهائياً.',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        if ($data['status_code'] === 410) {
            $data['to_path'] = null;
        }

        return $data;
    }
}
