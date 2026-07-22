<?php

namespace App\Http\Requests\Admin;

use App\Models\Post;
use App\Support\ArabicSlug;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
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
        $id = $this->route('post')?->id;

        return [
            'post_category_id' => ['required', 'exists:post_categories,id'],
            'title' => ['required', 'string', 'max:190'],
            'h1' => ['nullable', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', Rule::unique('posts', 'slug')->ignore($id)],
            'excerpt' => ['nullable', 'string', 'max:400'],
            'body' => ['nullable', 'string'],

            'cover_image_id' => ['nullable', 'exists:media,id'],
            'og_image_id' => ['nullable', 'exists:media,id'],

            'author_name' => ['nullable', 'string', 'max:120'],
            'reviewer_name' => ['nullable', 'string', 'max:120'],

            'seo_title' => ['nullable', 'string', 'max:70'],
            'seo_description' => ['nullable', 'string', 'max:160'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
            'focus_phrase' => ['nullable', 'string', 'max:120'],
            'is_indexable' => ['boolean'],

            'status' => ['required', Rule::in([Post::STATUS_DRAFT, Post::STATUS_SCHEDULED, Post::STATUS_PUBLISHED])],
            // Required for a scheduled article — "scheduled" with no date is
            // not a state the publishing rules can act on.
            'published_at' => ['nullable', 'date', Rule::requiredIf($this->input('status') === Post::STATUS_SCHEDULED)],
            'content_updated_at' => ['nullable', 'date'],
            'needs_review' => ['boolean'],

            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['exists:services,id'],
            'related_post_ids' => ['nullable', 'array'],
            'related_post_ids.*' => ['exists:posts,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'published_at.required' => 'حدّد تاريخ النشر للمقال المجدول.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $slug = $this->input('slug');
        $source = filled($slug) ? $slug : $this->input('title');

        $this->merge([
            'slug' => filled($source) ? ArabicSlug::make($source) : null,
            'is_indexable' => $this->boolean('is_indexable'),
            'needs_review' => $this->boolean('needs_review'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function postData(): array
    {
        $data = $this->safe()->except(['service_ids', 'related_post_ids']);

        if ($data['status'] === Post::STATUS_PUBLISHED && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
