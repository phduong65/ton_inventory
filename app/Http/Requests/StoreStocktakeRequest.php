<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStocktakeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id'          => ['nullable', 'exists:categories,id'],
            'note'                 => ['nullable', 'string', 'max:500'],
            'details'              => ['required', 'array', 'min:1'],
            'details.*.product_id' => ['required', 'exists:products,id'],
            'details.*.system_qty' => ['required', 'numeric', 'min:0'],
            'details.*.actual_qty' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'details.required'              => 'Phiếu kiểm kê phải có ít nhất một sản phẩm.',
            'details.min'                   => 'Phiếu kiểm kê phải có ít nhất một sản phẩm.',
            'details.*.product_id.required' => 'Thiếu mã sản phẩm.',
            'details.*.actual_qty.required' => 'Vui lòng nhập số lượng thực tế.',
            'details.*.actual_qty.min'      => 'Số lượng thực tế không được âm.',
        ];
    }
}
