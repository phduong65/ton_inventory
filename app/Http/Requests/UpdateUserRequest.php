<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('user')?->id;

        return [
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', "unique:users,email,{$id}"],
            'role'  => ['required', 'exists:roles,name'],
        ];
    }
}
