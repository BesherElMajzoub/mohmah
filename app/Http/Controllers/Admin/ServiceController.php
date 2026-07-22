<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceRequest;
use App\Models\Post;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.services.index', [
            'services' => Service::query()
                ->with('category')
                ->when($request->string('q')->trim()->value(), fn ($q, $term) => $q->where('title', 'like', "%{$term}%"))
                ->when($request->string('status')->value(), fn ($q, $status) => $q->where('status', $status))
                ->when($request->integer('category'), fn ($q, $id) => $q->where('service_category_id', $id))
                ->orderBy('service_category_id')
                ->orderBy('position')
                ->paginate(30)
                ->withQueryString(),
            'categories' => ServiceCategory::query()->orderBy('position')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.services.form', [
            'service' => new Service(['status' => Service::STATUS_DRAFT, 'is_indexable' => true]),
            ...$this->formOptions(),
        ]);
    }

    public function store(ServiceRequest $request): RedirectResponse
    {
        $service = Service::create($request->serviceData());

        $this->syncRelations($service, $request);

        return redirect()
            ->route('admin.services.edit', $service)
            ->with('status', 'تم إنشاء الخدمة.');
    }

    public function edit(Service $service): View
    {
        return view('admin.services.form', [
            'service' => $service->load(['relatedServices', 'posts']),
            ...$this->formOptions(),
        ]);
    }

    public function update(ServiceRequest $request, Service $service): RedirectResponse
    {
        $service->update($request->serviceData());

        $this->syncRelations($service, $request);

        return redirect()
            ->route('admin.services.edit', $service)
            ->with('status', 'تم حفظ التعديلات.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();

        return redirect()
            ->route('admin.services.index')
            ->with('status', 'تم حذف الخدمة.');
    }

    private function syncRelations(Service $service, ServiceRequest $request): void
    {
        // Positions are assigned from the submitted order so the editor's
        // arrangement is what the page renders.
        $related = collect($request->validated('related_service_ids', []))
            // A service listing itself as related would be a link to nowhere.
            ->reject(fn ($id) => (int) $id === $service->id)
            ->values()
            ->mapWithKeys(fn ($id, $i) => [$id => ['position' => $i]]);

        $service->relatedServices()->sync($related);

        $posts = collect($request->validated('post_ids', []))
            ->values()
            ->mapWithKeys(fn ($id, $i) => [$id => ['position' => $i]]);

        $service->posts()->sync($posts);
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'categories' => ServiceCategory::query()->orderBy('position')->get(),
            'allServices' => Service::query()->orderBy('title')->get(['id', 'title']),
            'allPosts' => Post::query()->orderBy('title')->get(['id', 'title']),
            'licenses' => config('site.licenses'),
        ];
    }
}
