<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class AuthValidate extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'token' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'token.required' => 'Debes enviar el Token para validarlo.',
        ];
    }
}
