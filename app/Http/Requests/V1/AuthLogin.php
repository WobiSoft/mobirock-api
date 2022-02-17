<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class AuthLogin extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'username' => 'required|numeric|min:8|exists:concesionarios,concesionario_numero',
            'password' => 'required',
            'remember' => 'nullable',
        ];
    }

    public function messages()
    {
        return [
            'username.required' => 'Debes enviar tu No. de Usuario.',
            'username.numeric'  => 'Este es un No. de Usuario inválido.',
            'username.min'      => 'El No. de Usuario debe contener al menos 8 dígitos.',
            'username.exists'   => 'Este No. de Usuario no existe en la base de datos.',
            'password.required' => 'Debes enviar tu Contraseña',
        ];
    }
}
