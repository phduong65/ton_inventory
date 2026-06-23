<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'type', 'status',
        'supplier_id', 'destination_id',
        'created_by', 'approved_by',
        'date', 'note', 'rejected_reason',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function attachments(): HasMany
    {
        return $this->hasMany(TransactionAttachment::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function stockLedger(): HasMany
    {
        return $this->hasMany(StockLedger::class);
    }

    public function isDraft(): bool    { return $this->status === 'draft'; }
    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }

    public function scopePending($query)  { return $query->where('status', 'pending'); }
    public function scopeApproved($query) { return $query->where('status', 'approved'); }
    public function scopeIn($query)       { return $query->where('type', 'IN'); }
    public function scopeOut($query)      { return $query->where('type', 'OUT'); }

    public static function generateCode(string $type): string
    {
        $prefix = match ($type) {
            'IN'         => 'PN',
            'OUT'        => 'PX',
            'ADJUSTMENT' => 'DC',
            default      => 'XX',
        };

        $year  = now()->format('Y');
        $count = static::where('type', $type)->whereYear('created_at', $year)->count() + 1;

        return sprintf('%s%s%04d', $prefix, $year, $count);
    }
}
