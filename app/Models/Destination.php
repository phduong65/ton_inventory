<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Destination extends Model
{
    protected $fillable = ['code', 'name', 'phone', 'manager', 'address', 'note'];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
