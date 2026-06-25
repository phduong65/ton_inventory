<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 'unit_id', 'sku', 'barcode', 'name',
        'default_price', 'min_stock', 'description', 'status',
    ];

    protected $casts = [
        'min_stock' => 'float',
    ];

    public function isBelowMinStock(): bool
    {
        return $this->min_stock !== null
            && $this->min_stock > 0
            && ($this->inventory?->quantity ?? 0) <= $this->min_stock;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function unitConversions(): HasMany
    {
        return $this->hasMany(UnitConversion::class)->with('unit');
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class);
    }

    public function transactionDetails(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function stockLedger(): HasMany
    {
        return $this->hasMany(StockLedger::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
