<?php

namespace App\Http\Requests\Admin;

use App\Models\Service;
use App\Support\ArabicSlug;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $id = $this->route('service')?->id;

        return [
            'service_category_id' => ['required', 'exists:service_categories,id'],
            'title' => ['required', 'string', 'max:190'],
            'h1' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', Rule::unique('services', 'slug')->ignore($id)],
            'summary' => ['nullable', 'string', 'max:300'],
            'overview' => ['nullable', 'string'],

            // Repeatable sections arrive as arrays from the admin form.
            'audience' => ['nullable', 'array'],
            'audience.*' => ['nullable', 'string', 'max:500'],
            'scope' => ['nullable', 'array'],
            'scope.*' => ['nullable', 'string', 'max:500'],
            'process' => ['nullable', 'array'],
            'process.*.title' => ['nullable', 'string', 'max:190'],
            'process.*.body' => ['nullable', 'string', 'max:1000'],
            'faqs' => ['nullable', 'array'],
            'faqs.*.question' => ['nullable', 'string', 'max:300'],
            'faqs.*.answer' => ['nullable', 'string', 'max:2000'],

            'license_keys' => ['nullable', 'array'],
            'license_keys.*' => ['string', Rule::in(array_column(config('site.licenses'), 'key'))],

            'disclaimer' => ['nullable', 'string', 'max:1000'],
            'og_image_id' => ['nullable', 'exists:media,id'],

            // Length limits reflect what search engines actually display —
            // longer values are not invalid, just truncated in results.
            'seo_title' => ['nullable', 'string', 'max:70'],
            'seo_description' => ['nullable', 'string', 'max:160'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
            'focus_phrase' => ['nullable', 'string', 'max:120'],
            'is_indexable' => ['boolean'],

            'status' => ['required', Rule::in([Service::STATUS_DRAFT, Service::STATUS_PUBLISHED])],
            'published_at' => ['nullable', 'date'],
            'needs_review' => ['boolean'],
            'position' => ['nullable', 'integer', 'min:0'],

            'related_service_ids' => ['nullable', 'array'],
            'related_service_ids.*' => ['exists:services,id'],
            'post_ids' => ['nullable', 'array'],
            'post_ids.*' => ['exists:posts,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Derive the slug from the H1 when the editor leaves it blank, and
        // normalise it when they fill it in — so a pasted Arabic title with
        // diacritics still yields a clean, typeable URL.
        $slug = $this->input('slug');
        $source = filled($slug) ? $slug : $this->input('h1', $this->input('title'));

        $this->merge([
            'slug' => filled($source) ? ArabicSlug::make($source) : null,
            'is_indexable' => $this->boolean('is_indexable'),
            'needs_review' => $this->boolean('needs_review'),
        ]);
    }

    /**
     * The validated payload, with empty repeater rows removed.
     *
     * The admin renders a few blank rows for convenience; persisting them
     * would leave empty bullets on the public page.
     *
     * @return array<string, mixed>
     */
    public function serviceData(): array
    {
        $data = $this->safe()->except(['related_service_ids', 'post_ids']);

        foreach (['audience', 'scope'] as $key) {
            $data[$key] = array_values(array_filter(
                (array) ($data[$key] ?? []),
                static fn ($v) => filled($v),
            ));
        }

        $data['process'] = array_values(array_filter(
            (array) ($data['process'] ?? []),
            static fn ($row) => filled($row['title'] ?? null) || filled($row['body'] ?? null),
        ));

        $data['faqs'] = array_values(array_filter(
            (array) ($data['faqs'] ?? []),
            static fn ($row) => filled($row['question'] ?? null) && filled($row['answer'] ?? null),
        ));

        $data['license_keys'] = array_values((array) ($data['license_keys'] ?? []));

        // Publishing without an explicit date means "now".
        if ($data['status'] === Service::STATUS_PUBLISHED && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
