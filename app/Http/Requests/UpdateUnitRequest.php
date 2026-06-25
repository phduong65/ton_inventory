<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUnitRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('unit')?->id;

        return [
            'code' => ['required', 'string', 'max:20', "unique:units,code,{$id}", 'regex:/^[A-Z0-9_]+$/'],
            'name' => ['required', 'string', 'max:50', "unique:units,name,{$id}"],
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
