<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StocktakeDetail extends Model
{
    protected $fillable = [
        'stocktake_id', 'product_id',
        'system_qty', 'actual_qty', 'variance',
    ];

    protected $casts = [
        'system_qty' => 'float',
        'actual_qty' => 'float',
        'variance'   => 'float',
    ];

    public function stocktake(): BelongsTo
    {
        return $this->belongsTo(Stocktake::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
