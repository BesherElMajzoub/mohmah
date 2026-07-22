<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use App\Support\ArabicSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.service-categories.index', [
            'categories' => ServiceCategory::query()
                ->withCount('services')
                ->orderBy('position')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.service-categories.form', ['category' => new ServiceCategory]);
    }

    public function store(Request $request): RedirectResponse
    {
        ServiceCategory::create($this->validated($request));

        return redirect()
            ->route('admin.service-categories.index')
            ->with('status', 'تم إنشاء المجال.');
    }

    public function edit(ServiceCategory $serviceCategory): View
    {
        return view('admin.service-categories.form', ['category' => $serviceCategory]);
    }

    public function update(Request $request, ServiceCategory $serviceCategory): RedirectResponse
    {
        $serviceCategory->update($this->validated($request, $serviceCategory->id));

        return redirect()
            ->route('admin.service-categories.index')
            ->with('status', 'تم حفظ التعديلات.');
    }

    public function destroy(ServiceCategory $serviceCategory): RedirectResponse
    {
        // Services cascade-delete with their category, which would silently
        // remove live pages. Refuse instead and let the editor move them.
        if ($serviceCategory->services()->exists()) {
            return back()->withErrors([
                'category' => 'لا يمكن حذف مجال يحتوي على خدمات. انقل الخدمات إلى مجال آخر أولاً.',
            ]);
        }

        $serviceCategory->delete();

        return redirect()
            ->route('admin.service-categories.index')
            ->with('status', 'تم حذف المجال.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'menu_label' => ['nullable', 'string', 'max:60'],
            'slug' => ['nullable', 'string', 'max:190', Rule::unique('service_categories', 'slug')->ignore($ignoreId)],
            'intro' => ['nullable', 'string', 'max:500'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['slug'] = ArabicSlug::make(filled($data['slug'] ?? null) ? $data['slug'] : $data['title']);
        $data['position'] ??= 0;

        return $data;
    }
}
