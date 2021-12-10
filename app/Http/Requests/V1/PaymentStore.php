<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class PaymentStore extends FormRequest
{
    public function authorize()
    {
        return !empty(request()->auth->user);
    }

    public function rules()
    {
        return [
            'method_id'  => 'required|exists:formas,forma_id',
            'account_id' => 'required|exists:cuentas,cuenta_id',
            'amount'     => 'required|numeric|min:100',
            'identifier' => 'required',
            'date'       => 'required|date_format:Y-m-d',
            'receipt'    => 'required',
        ];
    }

    public function messages()
    {
        return [
            'method_id.required'  => 'Debe seleccionar un método de pago.',
            'method_id.exists'    => 'El método de pago seleccionado no existe.',
            'account_id.required' => 'Debe seleccionar una cuenta.',
            'account_id.exists'   => 'La cuenta seleccionada no existe.',
            'amount.required'     => 'Debe ingresar un monto.',
            'amount.numeric'      => 'El monto debe ser un número.',
            'amount.min'          => 'El monto debe ser mayor a 100.',
            'identifier.required' => 'Debe ingresar el folio generado por la entidad bancaria.',
            'date.required'       => 'Debe ingresar la fecha del pago.',
            'date.date_format'    => 'La fecha debe tener el formato válido.',
            'receipt.required'    => 'Debe enviar el comprobante de pago.',
        ];
    }
}
