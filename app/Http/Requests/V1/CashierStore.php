<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class CashierStore extends FormRequest
{
    public function authorize()
    {
        $user = request()->auth->user;

        return !empty($user) && $user->type->id === 5;
    }

    public function rules()
    {
        return [
            'first_name' => 'required',
            'second_name' => 'nullable',
            'first_surname' => 'required',
            'second_surname' => 'nullable',
            'password' => 'required|confirmed',
            'email' => 'nullable|email',
            'mobile' => 'nullable|numeric|digits_between:10,10',
            'phone' => 'nullable|numeric|digits_between:10,10',
        ];
    }

    public function messages()
    {
        return [
            'first_name.required' => 'El primer nombre es requerido.',
            'first_surname.required' => 'El primer apellido es requerido.',
            'password.required' => 'La contraseña es requerida.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'email.email' => 'El correo electrónico no es válido.',
            'mobile.numeric' => 'El número celular debe incluir solo dígitos.',
            'mobile.digits_between' => 'El número celular debe tener 10 dígitos.',
            'phone.numeric' => 'El número de teléfono debe incluir solo dígitos.',
            'phone.digits_between' => 'El número de teléfono debe tener 10 dígitos.',
        ];
    }
}
