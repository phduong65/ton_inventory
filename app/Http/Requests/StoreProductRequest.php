<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'category_id'   => ['nullable', 'exists:categories,id'],
            'sku'           => ['required', 'string', 'max:50', 'unique:products'],
            'barcode'       => ['nullable', 'string', 'max:100', 'unique:products'],
            'name'          => ['required', 'string', 'max:200'],
            'unit'          => ['required', 'string', 'max:30'],
            'default_price' => ['nullable', 'numeric', 'min:0'],
            'description'   => ['nullable', 'string'],
            'status'        => ['required', 'in:active,inactive'],
        ];
    }
}
