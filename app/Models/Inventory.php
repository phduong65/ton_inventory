<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    protected $table = 'inventory';
    public $incrementing = false;
    protected $primaryKey = 'product_id';
    public $timestamps = false;

    protected $fillable = ['product_id', 'quantity', 'average_cost'];

    protected $casts = [
        'quantity'     => 'float',
        'average_cost' => 'float',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getTotalValueAttribute(): float
    {
        return $this->quantity * $this->average_cost;
    }
}
