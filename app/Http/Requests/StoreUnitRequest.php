<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUnitRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20', 'unique:units,code', 'regex:/^[A-Z0-9_]+$/'],
            'name' => ['required', 'string', 'max:50', 'unique:units,name'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Mã đơn vị không được để trống.',
            'code.unique'   => 'Mã đơn vị này đã tồn tại.',
            'code.regex'    => 'Mã đơn vị chỉ được chứa chữ in hoa, số và dấu gạch dưới.',
            'name.unique'   => 'Tên đơn vị này đã tồn tại.',
        ];
    }
}
