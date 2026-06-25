<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'type'                            => ['required', 'in:IN,OUT'],
            'date'                            => ['required', 'date'],
            'supplier_id'                     => ['required_if:type,IN', 'nullable', 'exists:suppliers,id'],
            'destination_id'                  => ['required_if:type,OUT', 'nullable', 'exists:destinations,id'],
            'note'                            => ['nullable', 'string', 'max:500'],
            'details'                         => ['required', 'array', 'min:1'],
            'details.*.product_id'            => ['required', 'exists:products,id'],
            'details.*.unit_id'               => ['required', 'exists:units,id'],
            'details.*.conversion_factor'     => ['required', 'numeric', 'min:0.0001'],
            'details.*.qty'                   => ['required', 'numeric', 'min:0.001'],
            'details.*.price'                 => ['nullable', 'numeric', 'min:0'],
            'details.*.discount'              => ['nullable', 'numeric', 'min:0', 'max:100'],
            'details.*.vat'                   => ['nullable', 'numeric', 'min:0', 'max:100'],
            'images'                          => ['nullable', 'array', 'max:10'],
            'images.*'                        => ['file', 'image', 'max:5120'],
        ];
    }
}
