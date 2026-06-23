<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionDetail extends Model
{
    protected $fillable = [
        'transaction_id', 'product_id',
        'qty', 'price', 'discount', 'vat', 'amount',
    ];

    protected $casts = [
        'qty'      => 'float',
        'price'    => 'float',
        'discount' => 'float',
        'vat'      => 'float',
        'amount'   => 'float',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function computeAmount(): float
    {
        return $this->qty * $this->price * (1 - $this->discount / 100) * (1 + $this->vat / 100);
    }
}
