<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class RechargePrepaid extends FormRequest
{
    public function authorize()
    {
        $user = request()->auth->user;

        return in_array($user->type->id, [5, 6, 9, 10]);
    }

    public function rules()
    {
        return [
            'brand'     => 'required|exists:marcas,marca_id',
            'product'   => 'required|exists:proveedores_productos,producto_id',
            'reference' => 'required|regex:/^[1-9]{1}[0-9]{9}$/|confirmed',
            'uuid'      => 'required|uuid',
        ];
    }

    public function messages()
    {
        return [
            'brand.required'      => 'Debes elegir la Marca de la Recarga.',
            'brand.exists'        => 'La Marca de la Recarga no existe.',
            'product.required'    => 'Debes elegir el Monto de la Recarga.',
            'product.exists'      => 'El Monto de la Recarga no existe.',
            'reference.required'  => 'Debes ingresar el No. Celular.',
            'reference.regex'     => 'El No. Celular debe tener 10 dígitos.',
            'reference.confirmed' => 'Los No. Celulares no coinciden.',
            'uuid.required'       => 'Debes ingresar el UUID de la Recarga.',
            'uuid.uuid'           => 'El UUID de la Recarga no es válido.',
        ];
    }
}
