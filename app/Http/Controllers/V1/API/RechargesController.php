<?php

namespace App\Http\Controllers\V1\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\RechargePrepaid;
use App\Models\Brand;
use App\Models\Provider;
use App\Models\ProviderProduct;
use App\Models\Sale;
use App\Models\Transaction;
use App\Models\TransactionFailed;
use App\Models\TransactionProcessing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RechargesController extends Controller
{
    public function create(RechargePrepaid $request)
    {
        //sleep(12);

        $user = request()->auth->parent;
        $config = $user->config;

        $data = (object) $request->validated();

        $brand = Brand::find($data->brand);
        $product = ProviderProduct::find($data->product);

        /** Saldo Suficiente */
        if (!$config->balance_tae or $config->balance_tae < $product->amount)
        {
            return response()->json([
                'message' => 'No cuentas con Saldo suficiente para realizar esta Recarga.',
            ], 400);
        }
        /** END: Saldo Suficiente */

        /** Transacción en proceso */
        $processing = TransactionProcessing::where('user_id', $user->id)->first();

        if ($processing)
        {
            return response()->json([
                'message' => 'Ya se encuentra una transacción en proceso, espera a que finalice el proceso.',
            ], 400);
        }
        /** END: Transacción en proceso */

        /** 5 minutos */
        $five_minutes_ago = date('Y-m-d H:i:s', strtotime('-5 minutes'));

        $recent_sale = Sale::select('*')
            ->with(['brand'])
            ->whereVentaConcesionario($user->id)
            ->whereVentaReferencia($data->reference)
            ->where('venta_procesada', '>', $five_minutes_ago)
            ->where('venta_status', '!=', 0)
            ->first();

        if ($recent_sale)
        {
            return response()->json([
                'message' => 'Se realizó una Recarga a esta referencia hace menos de 5 minutos, espera para poder realizarla nuevamente.',
                'data' => [
                    'brand' => $recent_sale->brand->name,
                    'amount' => $recent_sale->amount,
                    'authorization_code' => $recent_sale->authorization_code,
                    'processed_at' => $recent_sale->processed_at,
                    'status' => $recent_sale->status,
                ],
            ], 400);
        }
        /** END: 5 minutos */

        /** Setup de Proveedores */
        $provider = Provider::find($product->producto_proveedor);

        $provider_config = config('providers.' . $provider->class);
        $provider_service = app(('\\App\\Services\\Providers\\' . ucfirst(Str::camel($provider->class))));

        $provider_service->set($provider, $provider_config);

        $provider_balance = $provider_service->balance()->balance;

        Log::debug('========== SALDO ==========');
        Log::debug(date('Y-m-d H:i:s ') . $provider->name . ': ' . $provider_balance);
        Log::debug('========== SALDO ==========');
        /** END: Setup de Proveedores */

        /** Saldo Suficiente (Proveedor) */
        /* if (!$provider_balance or $provider_balance < $product->amount)
        {
            return response()->json([
                'message' => 'El operador (' . $brand->name . ') esta presentando una intermitencia ajena a nuestro servicio, intenta nuevamente en un momento.',
            ], 400);
        } */
        /** END: Saldo Suficiente (Proveedor) */

        /** Transacción en proceso (crear) */
        $identifier = $provider_service->id();

        $processing = TransactionProcessing::create([
            'user_id' => $user->id,
            'provider_id' => $product->id,
            'uuid' => $data->uuid,
            'identifier' => $identifier->data,
        ]);
        /** END: Transacción en proceso (crear) */

        /** Se realiza la Transacción */
        $transaction = $provider_service->transaction($data, $user, $config, $brand, $product, $identifier->data);
        /** END: Se realiza la Transacción */

        if (!$transaction->status)
        {
            return response()->json([
                'message' => $transaction->message,
            ], 400);
        }

        return response()->json([
            'message' => $transaction->message,
            'data' => [
                'brand' => $brand->name,
                'mobile_support' => $brand->mobile_support,
                'phone_support' => $brand->phone_support,
                'amount' => $product->amount,
                'reference' => $data->reference,
                'authorization_code' => $transaction->data->sale->authorization_code,
                'created_at' => $transaction->data->sale->created_at,
            ],
        ]);
    }

    public function search(Request $request, $uuid, $attemps)
    {
        $user = request()->auth->parent;
        $attemps = intval($attemps);

        $processing = TransactionProcessing::orderBy('id', 'DESC')
            ->whereUserId($user->id)
            ->whereUuid($uuid)
            ->first();

        if ($processing)
        {
            if ($attemps >= 10)
            {
                $processing->delete();

                return response()->json([
                    'message' => 'El intento de recarga finalizó sin respuesta por parte del operador.',
                ], 400);
            }

            return response()->json([
                'message' => 'La Transacción se encuentra en proceso.'
            ], 102);
        }

        $transaction = Transaction::orderBy('transaccion_id', 'DESC')
            ->whereTransaccionOrigen($user->id)
            ->whereTransaccionUuid($uuid)
            ->whereTransaccionStatus(1)
            ->first();

        if ($transaction)
        {
            return response()->json([
                'message' => 'Recarga exitosa',
                'data' => [
                    'brand' => $transaction->sale->brand->name,
                    'mobile_support' => $transaction->sale->brand->mobile_support,
                    'phone_support' => $transaction->sale->brand->phone_support,
                    'amount' => $transaction->sender->amount,
                    'reference' => $transaction->reference,
                    'authorization_code' => $transaction->authorization_code,
                    'created_at' => $transaction->processed_at,
                ],
            ]);
        }

        $transaction_failed = TransactionFailed::orderBy('id', 'DESC')
            ->whereCliente($user->id)
            ->whereUuid($uuid)
            ->first();

        if ($transaction_failed)
        {
            $provider = Provider::find($transaction_failed->provider_id);

            $provider_service = app(('\\App\\Services\\Providers\\' . ucfirst(Str::camel($provider->class))));

            $messages = $provider_service->response(
                (!empty($transaction_failed->code) ? $transaction_failed->code : NULL),
                (!empty($transaction_failed->message) ? $transaction_failed->message : NULL)
            );

            return response()->json([
                'message' => $messages->message,
            ], 400);
        }

        return response()->json([
            'message' => 'El intento de recarga finalizó sin respuesta por parte del operador, intenta nuevamente.',
        ], 400);
    }
}
