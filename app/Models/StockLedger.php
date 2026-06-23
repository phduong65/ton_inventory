<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLedger extends Model
{
    public $timestamps = false;

    protected $table = 'stock_ledger';

    protected $fillable = [
        'transaction_id', 'product_id', 'type',
        'qty', 'before_qty', 'after_qty', 'cost_price',
        'created_at',
    ];

    protected $casts = [
        'qty'        => 'float',
        'before_qty' => 'float',
        'after_qty'  => 'float',
        'cost_price' => 'float',
        'created_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
