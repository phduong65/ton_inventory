<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TransactionAttachment extends Model
{
    protected $fillable = ['transaction_id', 'path', 'original_name', 'size', 'mime_type'];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    public function getHumanSizeAttribute(): string
    {
        $kb = $this->size / 1024;
        return $kb > 1024 ? round($kb / 1024, 1).' MB' : round($kb, 0).' KB';
    }
}
