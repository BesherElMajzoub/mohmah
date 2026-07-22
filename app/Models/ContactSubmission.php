<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name', 'phone', 'email', 'subject', 'message',
    'page_path', 'utm_source', 'utm_campaign', 'gclid', 'ip_hash',
])]
class ContactSubmission extends Model
{
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }
}
