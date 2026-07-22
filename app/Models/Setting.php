<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'value', 'type', 'group'])]
class Setting extends Model
{
    /**
     * Cast the stored string according to the row's declared type.
     *
     * The value column is text for everything, so the type column is what
     * turns "1" into true and a JSON string back into an array.
     */
    public function castValue(): mixed
    {
        return match ($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => $this->value === null ? null : (int) $this->value,
            'json', 'array' => json_decode((string) $this->value, true) ?: [],
            default => $this->value,
        };
    }
}
