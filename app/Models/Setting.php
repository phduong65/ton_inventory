<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $primaryKey = 'key';
    protected $keyType    = 'string';
    public    $incrementing = false;

    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $record = static::find($key);

        if ($record === null) {
            return $default;
        }

        $value = $record->value;

        if ($value === '1' || $value === 'true') return true;
        if ($value === '0' || $value === 'false') return false;

        return $value;
    }

    public static function set(string $key, mixed $value): void
    {
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        }

        static::updateOrCreate(['key' => $key], ['value' => (string) $value]);
    }
}
