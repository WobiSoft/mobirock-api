<?php

namespace App\Http\Controllers\V1\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\PaymentStore;
use App\Models\Payment;
use App\Notifications\PaymentReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class PaymentsController extends Controller
{
    public function index()
    {
        //
    }

    public function store(PaymentStore $request)
    {
        $user = request()->auth->user;
        $parent = request()->auth->parent;
        $data = $request->validated();

        $payment = new Payment([
            'pago_concesionario' => $parent->id,
            'pago_forma' => $data['method_id'],
            'pago_cuenta' => $data['account_id'],
            'pago_monto' => $data['amount'],
            'pago_cdr' => $data['identifier'],
            'pago_registrado_por' => $user->id,
            'pago_fecha' => $data['date'],
        ]);

        $duplicated = $payment->checkDuplicated();

        if ($duplicated)
        {
            return response()->json([
                'message' => 'Al parecer ya has reportado este pago, si crees que es una confusión, comunícate con tu Distribuidor.'
            ], 409);
        }

        if (!$this->validatedFile($data['receipt']))
        {
            return response()->json([
                'message' => 'Debes enviar un comprobante de pago válido.'
            ], 400);
        }

        $payment->pago_cdr = $payment->checkIdentifier();

        $payment->save();

        $path = (date('Y/m/d/') . $parent->id . '/' . $payment->id . '_payment_' . time());
        $receipt = $this->saveFile($data['receipt'], $path);

        $payment->update([
            'pago_comprobante' => $receipt
        ]);

        Notification::route('mail', $payment->user->email)->notify(new PaymentReceived($payment));

        return response()->json([
            'message' => 'El pago se ha registrado correctamente.'
        ]);
    }

    public function show(Payment $purchase)
    {
        //
    }

    public function update(Request $request, Payment $purchase)
    {
        //
    }

    public function destroy(Payment $purchase)
    {
        //
    }
}
