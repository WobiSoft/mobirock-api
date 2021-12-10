<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class AuthRenew extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'password' => 'required|confirmed',
            'remember' => 'required|boolean',
            'token' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'password.required' => 'Debes enviar tu Nueva Contraseña.',
            'password.confirmed' => 'Debes confirmar tu Nueva Contraseña, y no coinciden.',
            'remember' => 'Debes establecer si deseas recordar tu sesión.',
            'token' => 'Debes enviar el Token de Renovación.',
        ];
    }
}
