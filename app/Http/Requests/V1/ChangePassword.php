<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class ChangePassword extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'current' => 'required',
            'password' => 'required|confirmed',
        ];
    }

    public function messages()
    {
        return [
            'current.required' => 'Debes enviar tu contraseña actual.',
            'password.required' => 'Debes enviar tu nueva contraseña.',
            'password.confirmed' => 'Debes confirmar tu nueva contraseña.',
        ];
    }
}
