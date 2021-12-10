<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class AuthCode extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'code' => 'required|numeric|min:6',
            'token' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'code.required' => 'Debes enviar tu Código de Renovación.',
            'code.numeric' => 'Debes enviar un Código de Renovación válido.',
            'code.min' => 'Debes enviar un Código de Renovación válido.',
            'token' => 'Debes enviar el Token de Renovación.',
        ];
    }
}
