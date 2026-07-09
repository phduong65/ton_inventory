<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('product')?->id;

        return [
            'category_id'                => ['nullable', 'exists:categories,id'],
            'sku'                        => ['required', 'string', 'max:50', "unique:products,sku,{$id}"],
            'barcode'                    => ['nullable', 'string', 'max:100', "unique:products,barcode,{$id}"],
            'name'                       => ['required', 'string', 'max:200'],
            'unit_id'                    => ['required', 'exists:units,id'],
            'default_price'              => ['nullable', 'numeric', 'min:0'],
            'min_stock'                  => ['nullable', 'numeric', 'min:0'],
            'description'                => ['nullable', 'string'],
            'status'                     => ['required', 'in:active,inactive'],
            'image'                      => ['nullable', 'image', 'max:2048'],
            'remove_image'               => ['nullable', 'boolean'],
            'conversions'                => ['nullable', 'array'],
            'conversions.*.unit_id'      => ['required_with:conversions', 'exists:units,id'],
            'conversions.*.factor'       => ['required_with:conversions', 'numeric', 'min:0.0001'],
            'conversions.*.note'         => ['nullable', 'string', 'max:200'],
        ];
    }
}
