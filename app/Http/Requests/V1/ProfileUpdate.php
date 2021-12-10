<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdate extends FormRequest
{
    public function authorize()
    {
        return !empty(request()->auth->user);
    }

    public function rules()
    {
        return [
            'first_name' => 'required',
            'second_name' => 'nullable',
            'first_surname' => 'required',
            'second_surname' => 'nullable',
            'email' => 'nullable|email',
            'mobile' => 'nullable|numeric|digits_between:10,10',
            'phone' => 'nullable|numeric|digits_between:10,10',
            'business' => 'nullable',
        ];
    }

    public function messages()
    {
        return [
            'first_name.required' => 'El primer nombre es requerido.',
            'first_surname.required' => 'El primer apellido es requerido.',
            'email.email' => 'El correo electrónico no es válido.',
            'mobile.numeric' => 'El número celular debe incluir solo dígitos.',
            'mobile.digits_between' => 'El número celular debe tener 10 dígitos.',
            'phone.numeric' => 'El número de teléfono debe incluir solo dígitos.',
            'phone.digits_between' => 'El número de teléfono debe tener 10 dígitos.',
        ];
    }
}
