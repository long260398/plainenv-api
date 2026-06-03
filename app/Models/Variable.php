<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class Variable extends Model
{
    protected $fillable = ['environment_id', 'key', 'value'];

    public function environment(): BelongsTo
    {
        return $this->belongsTo(Environment::class);
    }

    public function setValueAttribute(string $value): void
    {
        $this->attributes['value'] = Crypt::encryptString($value);
    }

    public function getValueAttribute(string $value): string
    {
        return Crypt::decryptString($value);
    }
}
