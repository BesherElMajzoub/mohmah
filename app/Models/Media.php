<?php

namespace App\Models;

use App\Support\Url;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'disk', 'path', 'original_name', 'mime_type', 'size',
    'width', 'height', 'alt_ar', 'caption_ar', 'uploaded_by',
])]
class Media extends Model
{
    protected $table = 'media';

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Absolute URL on the canonical host — required by Open Graph and JSON-LD,
     * which reject relative image paths.
     */
    public function absoluteUrl(): string
    {
        $url = $this->url();

        return str_starts_with($url, 'http') ? $url : Url::host().$url;
    }

    /**
     * Images without Arabic alt text are flagged in the admin media library.
     */
    public function isMissingAlt(): bool
    {
        return str_starts_with($this->mime_type, 'image/')
            && trim((string) $this->alt_ar) === '';
    }
}
