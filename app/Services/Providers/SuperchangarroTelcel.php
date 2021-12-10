<?php

namespace App\Services\Providers;

use App\Models\Sale;
use App\Models\Transaction;
use App\Models\TransactionFailed;
use App\Models\TransactionProcessing;
use App\Models\UserConfig;
use Illuminate\Support\Facades\Log;
use SoapClient;

class SuperchangarroTelcel
{
    public $data;
    public $user;
    public $userConfig;
    public $provider;
    public $config;
    public $brand;
    public $product;
    public $identifier;

    public function set($provider, $config)
    {
        $this->provider = $provider;
        $this->config = $config;
    }

    public function id()
    {
        $start = date('Y-m-d H:i:s');

        $options = [
            'exceptions'         => true,
            'trace'              => 1,
            'keep_alive'         => false,
            'connection_timeout' => 10,
        ];

        $soapClient = new SoapClient($this->config->url, $options);

        $requestData = [
            'usuario'  => $this->config->user,
            'password' => $this->config->key,
        ];

        try
        {
            $post = $soapClient->GetCodRecarga($requestData);

            if (empty($post->GetCodRecargaResult))
            {
                TransactionFailed::create([
                    'cliente'          => $this->user->id,
                    'cliente_nombre'   => $this->user->name . ' (' . $this->user->business->name . ')',
                    'proveedor'        => $this->provider->id,
                    'proveedor_nombre' => $this->provider->name,
                    'iniciada'         => $start,
                    'finalizada'       => date('Y-m-d H:i:s'),
                    'descripcion'      => 'REFERENCIA: NO SE OBTUVO CÓDIGO PARA LA RECARGA',
                    'request'          => $requestData,
                    'response'         => $post,
                    'marca'            => $this->brand->id,
                    'marca_nombre'     => $this->brand->name,
                    'referencia'       => $this->data->reference,
                    'monto'            => $this->product->amount,
                    'uuid'             => NULL,
                    'cdr'              => NULL,
                ]);

                return (object) [
                    'status' => FALSE,
                    'data'   => NULL,
                ];
            }

            return (object) [
                'status' => TRUE,
                'data' => $post->GetCodRecargaResult,
            ];
        }
        catch (\Exception $e)
        {
            TransactionFailed::create([
                'cliente'          => $this->user->id,
                'cliente_nombre'   => $this->user->name . ' (' . $this->user->business->name . ')',
                'proveedor'        => $this->provider->id,
                'proveedor_nombre' => $this->provider->name,
                'iniciada'         => $start,
                'finalizada'       => date('Y-m-d H:i:s'),
                'descripcion'      => 'REFERENCIA: OCURRIÓ UN ERROR DE TIMEOUT Y NO SE OBTUVO CÓDIGO PARA LA RECARGA',
                'request'          => $requestData,
                'response'         => $e->getMessage(),
                'marca'            => $this->brand->id,
                'marca_nombre'     => $this->brand->name,
                'referencia'       => $this->data->reference,
                'monto'            => $this->product->amount,
                'uuid'             => NULL,
                'cdr'              => NULL,
            ]);

            return (object) [
                'status' => FALSE,
                'data'   => NULL,
            ];
        }
    }

    public function balance()
    {
        $options = [
            'exceptions'         => true,
            'trace'              => 1,
            'keep_alive'         => false,
            'connection_timeout' => 10,
        ];

        $soapClient = new \SoapClient($this->config->url, $options);

        $requestData = [
            'usuario'  => $this->config->user,
            'password' => $this->config->key,
        ];

        try
        {
            $post = $soapClient->GetSaldo($requestData);

            $result = json_decode(json_encode(simplexml_load_string('<?xml version="1.0"?><operators>' . $post->GetSaldoResult . '</operators>')));

            $balance = 0;

            foreach ($result->operator as $item)
            {
                if ($item->name === 'TELCEL')
                {
                    $balance = floatval($item->balance);
                }
            }

            $balance = ($balance * ($this->provider->proveedor_saldo_negativo ? -1 : 1));

            return (object) ['status' => TRUE, 'balance' => $balance];
        }
        catch (\Exception $e)
        {
            Log::error('========== Error Consultando Saldo: Superchangarro Telcel ==========');
            Log::error($e);
            Log::error('========== Error Consultando Saldo: Superchangarro Telcel ==========');

            return (object) ['status' => FALSE, 'message' => 'Ocurrió un error al consultar el saldo.', 'balance' => 0];
        }
    }

    public function transaction($data, $user, $userConfig, $brand, $product, $identifier)
    {
        $this->data = $data;
        $this->user = $user;
        $this->userConfig = $userConfig;
        $this->brand = $brand;
        $this->product = $product;
        $this->identifier = $identifier;

        $start = date('Y-m-d H:i:s');

        $processing = TransactionProcessing::orderBy('id', 'DESC')
            ->whereUuid($this->data->uuid)
            ->whereUserId($this->user->id)
            ->first();

        $options = [
            'exceptions'         => true,
            'trace'              => 1,
            'keep_alive'         => false,
            'connection_timeout' => 10,
        ];

        $soapClient = new SoapClient($this->config->url, $options);

        $requestData = [
            'CodRegarga' => $this->identifier,
            'usuario'    => $this->config->user,
            'skuCode'    => $product->code,
            'numcell'    => $data->reference,
            'monto'      => $product->amount
        ];

        try
        {
            if (config('app.env') === 'local')
            {
                $post = (object) [
                    'RecargameResult' => (object) [
                        'respCode' => '00',
                        'description' => 'Recarga Exitosa',
                        'autorizacion' => rand(100000000, 999999999),
                    ]
                ];
            }
            else
            {
                $post = $soapClient->Recargame($requestData);
            }

            /** Transaccion con respuesta vacía */
            if (!isset($post->RecargameResult) or !isset($post->RecargameResult->respCode))
            {
                TransactionFailed::create([
                    'cliente'          => $this->user->id,
                    'cliente_nombre'   => $this->user->name . ' (' . $this->user->business->name . ')',
                    'proveedor'        => $this->provider->id,
                    'proveedor_nombre' => $this->provider->name,
                    'iniciada'         => $start,
                    'finalizada'       => date('Y-m-d H:i:s'),
                    'descripcion'      => 'RECARGA: NO SE EJECUTÓ LA TRANSACCIÓN',
                    'request'          => $requestData,
                    'response'         => $post,
                    'marca'            => $this->brand->id,
                    'marca_nombre'     => $this->brand->name,
                    'referencia'       => $this->data->reference,
                    'monto'            => $this->product->amount,
                    'uuid'             => $this->data->uuid,
                    'cdr'              => $this->identifier,
                ]);

                return (object) [
                    'status' => FALSE,
                    'message' => 'El operador (' . $this->brand->name . ') no ha respondido a la solicitud de la transacción, intenta nuevamente.'
                ];
            }
            /** END: Transaccion con respuesta vacía */

            $response = $post->RecargameResult;

            /** Transacción en proceso o pendiente */
            if ($response->respCode == '1' or $response->respCode == '2')
            {
                TransactionFailed::create([
                    'cliente'          => $this->user->id,
                    'cliente_nombre'   => $this->user->name . ' (' . $this->user->business->name . ')',
                    'proveedor'        => $this->provider->id,
                    'proveedor_nombre' => $this->provider->name,
                    'codigo'           => (isset($response->respCode) ? $response->respCode : NULL),
                    'error'            => (isset($response->description) ? $response->description : NULL),
                    'iniciada'         => $start,
                    'finalizada'       => date('Y-m-d H:i:s'),
                    'descripcion'      => 'RECARGA: SE RECIBIÓ RESPUESTA DE ' . ($response->respCode == '1' ? 'OPERACIÓN PENDIENTE' : 'OPERACIÓN EN PROGRESO') . '.',
                    'request'          => $requestData,
                    'response'         => $response,
                    'marca'            => $this->brand->id,
                    'marca_nombre'     => $this->brand->name,
                    'referencia'       => $this->data->reference,
                    'monto'            => $this->product->amount,
                    'uuid'             => $this->data->uuid,
                    'cdr'              => $this->identifier,
                ]);

                sleep(10);

                return $this->find();
            }
            /** END: Transacción en proceso o pendiente */

            /** Transacción fallida */
            if ($response->respCode != '0')
            {
                TransactionFailed::create([
                    'cliente'          => $this->user->id,
                    'cliente_nombre'   => $this->user->name . ' (' . $this->user->business->name . ')',
                    'proveedor'        => $this->provider->id,
                    'proveedor_nombre' => $this->provider->name,
                    'codigo'           => (isset($response->respCode) ? $response->respCode : NULL),
                    'error'            => (isset($response->description) ? $response->description : NULL),
                    'iniciada'         => $start,
                    'finalizada'       => date('Y-m-d H:i:s'),
                    'descripcion'      => 'RECARGA: SE RECIBIÓ RESPUESTA CON CÓDIGO NO EXITOSO',
                    'request'          => $requestData,
                    'response'         => $response,
                    'marca'            => $this->brand->id,
                    'marca_nombre'     => $this->brand->name,
                    'referencia'       => $this->data->reference,
                    'monto'            => $this->product->amount,
                    'uuid'             => $this->data->uuid,
                    'cdr'              => $this->identifier,
                ]);

                $processing->delete();

                $messages = $this->response((isset($response->respCode) ? $response->respCode : NULL), (isset($response->description) ? $response->description : NULL));

                return (object) [
                    'status' => FALSE,
                    'message' => $messages->message
                ];
            }
            /** END: Transacción fallida */

            /** Transacción exitosa */
            $end = date('Y-m-d H:i:s');

            /** Venta creada */
            $sale = Sale::create([
                'venta_concesionario' => $this->user->id,
                'venta_uuid'          => $this->data->uuid,
                'venta_producto'      => $this->product->id,
                'venta_proveedor'     => $this->provider->id,
                'venta_marca'         => $this->brand->id,
                'venta_referencia'    => $this->data->reference,
                'venta_importe'       => $this->product->amount,
                'venta_creada'        => $start,
                'venta_creada_por'    => request()->auth->user->id,
                'venta_procesada'     => $end,
                'venta_cdr'           => $this->identifier,
                'venta_cda'           => (isset($response->autorizacion) ? $response->autorizacion : NULL),

                'venta_extra' => [
                    'start'  => $this->user->config->balance_tae,
                    'amount' => $this->product->amount,
                    'end'  => ($this->user->config->balance_tae - $this->product->amount),
                ],

                'venta_codigo' => '00',
                'venta_status' => 1,
            ]);
            /** END: Venta creada */

            /** Transacción creada */
            $transaction = Transaction::create([
                'transaccion_concesionario'           => $this->user->id,
                'transaccion_sesion'                  => request()->auth->session->id,
                'transaccion_origen'                  => $this->user->id,
                'transaccion_generada'                => $start,
                'transaccion_procesada'               => $end,
                'transaccion_tipo'                    => 1,
                'transaccion_producto'                => 1,
                'transaccion_origen_saldo_inicial'    => $this->user->config->balance_tae,
                'transaccion_origen_saldo_monto'      => $this->product->amount,
                'transaccion_origen_saldo_comision'   => 0,
                'transaccion_origen_saldo_final'      => ($this->user->config->balance_tae - $this->product->amount),
                'transaccion_ip'                      => request()->ip(),
                'transaccion_referencia'              => $this->data->reference,
                'transaccion_cdr'                     => $this->identifier,
                'transaccion_uuid'                    => $this->data->uuid,
                'transaccion_cda'                     => (isset($response->autorizacion) ? $response->autorizacion : NULL),
                'transaccion_proveedor'               => $this->provider->id,
                'transaccion_respuesta'               => json_encode($response),
                'transaccion_venta'                   => $sale->id,
                'transaccion_request'                 => $requestData,
                'transaccion_response'                => $response,
                'transaccion_status'                  => 1,
            ]);
            /** END: Transacción creada */

            /** Saldo Actualizado */
            $userConfig = UserConfig::whereConfigConcesionario($this->user->id)->first();
            $userConfig->decrement('config_saldo', $this->product->amount);
            /** END: Saldo Actualizado */

            $processing->delete();

            return (object) [
                'status' => TRUE,
                'message' => 'Recarga exitosa',
                'data' => (object) [
                    'transaction' => $transaction,
                    'sale' => $sale,
                ]
            ];
            /** END: Transacción exitosa */
        }
        catch (\Exception $e)
        {
            TransactionFailed::create([
                'cliente'          => $this->user->id,
                'cliente_nombre'   => $this->user->name . ' (' . $this->user->business->name . ')',
                'proveedor'        => $this->provider->id,
                'proveedor_nombre' => $this->provider->name,
                'iniciada'         => $start,
                'finalizada'       => date('Y-m-d H:i:s'),
                'descripcion'      => 'RECARGA: ERROR DE TIMEOUT, NO SE OBTUVO RESPUESTA',
                'request'          => $requestData,
                'response'         => $e->getMessage(),
                'marca'            => $this->brand->id,
                'marca_nombre'     => $this->brand->name,
                'referencia'       => $this->data->reference,
                'monto'            => $this->product->amount,
                'uuid'             => $this->data->uuid,
                'cdr'              => $this->identifier,
            ]);

            sleep(10);

            return $this->find();
        }
    }

    public function find($attemps = 1)
    {
        $start = date('Y-m-d H:i:s');

        $processing = TransactionProcessing::orderBy('id', 'DESC')
            ->whereUuid($this->data->uuid)
            ->whereUserId($this->user->id)
            ->first();

        if ($attemps > 3)
        {
            $processing->delete();

            return (object) [
                'status' => FALSE,
                'message' => 'El operador ' . $this->brand->name . ' no ha respondido a la solicitud de recarga, intentalo nuevamente.'
            ];
        }

        $attemps++;

        $options = [
            'exceptions'         => true,
            'trace'              => 1,
            'keep_alive'         => false,
            'connection_timeout' => 10,
        ];

        $soapClient = new SoapClient($this->config->url, $options);

        $requestData = [
            'usuario'       => $this->config->user,
            'GetCodRegarga' => $this->identifier,
        ];

        try
        {
            $post = $soapClient->BuscarRecarga($requestData);

            /** Transaccion con respuesta vacía */
            if (!isset($post->BuscarRecargaResult) or !isset($post->BuscarRecargaResult->respCode))
            {
                TransactionFailed::create([
                    'cliente'          => $this->user->id,
                    'cliente_nombre'   => $this->user->name . ' (' . $this->user->business->name . ')',
                    'proveedor'        => $this->provider->id,
                    'proveedor_nombre' => $this->provider->name,
                    'iniciada'         => $start,
                    'finalizada'       => date('Y-m-d H:i:s'),
                    'descripcion'      => 'CONSULTA: NO SE EJECUTÓ LA CONSULTA',
                    'request'          => $requestData,
                    'response'         => $post,
                    'marca'            => $this->brand->id,
                    'marca_nombre'     => $this->brand->name,
                    'referencia'       => $this->data->reference,
                    'monto'            => $this->product->amount,
                    'uuid'             => $this->data->uuid,
                    'cdr'              => $this->identifier,
                ]);

                sleep(10);

                return $this->find($attemps);
            }
            /** END: Transaccion con respuesta vacía */

            $response = $post->BuscarRecargaResult;

            /** Transacción en proceso o pendiente */
            if ($response->respCode == '1' or $response->respCode == '2')
            {
                TransactionFailed::create([
                    'cliente'          => $this->user->id,
                    'cliente_nombre'   => $this->user->name . ' (' . $this->user->business->name . ')',
                    'proveedor'        => $this->provider->id,
                    'proveedor_nombre' => $this->provider->name,
                    'codigo'           => (isset($response->respCode) ? $response->respCode : NULL),
                    'error'            => (isset($response->description) ? $response->description : NULL),
                    'iniciada'         => $start,
                    'finalizada'       => date('Y-m-d H:i:s'),
                    'descripcion'      => 'CONSULTA: SE RECIBIÓ RESPUESTA DE ' . ($response->respCode == '1' ? 'OPERACIÓN PENDIENTE' : 'OPERACIÓN EN PROGRESO') . '.',
                    'request'          => $requestData,
                    'response'         => $response,
                    'marca'            => $this->brand->id,
                    'marca_nombre'     => $this->brand->name,
                    'referencia'       => $this->data->reference,
                    'monto'            => $this->product->amount,
                    'uuid'             => $this->data->uuid,
                    'cdr'              => $this->identifier,
                ]);

                sleep(10);

                return $this->find($attemps);
            }
            /** END: Transacción en proceso o pendiente */

            /** Transacción fallida */
            if ($response->respCode != '0')
            {
                TransactionFailed::create([
                    'cliente'          => $this->user->id,
                    'cliente_nombre'   => $this->user->name . ' (' . $this->user->business->name . ')',
                    'proveedor'        => $this->provider->id,
                    'proveedor_nombre' => $this->provider->name,
                    'codigo'           => (isset($response->respCode) ? $response->respCode : NULL),
                    'error'            => (isset($response->description) ? $response->description : NULL),
                    'iniciada'         => $start,
                    'finalizada'       => date('Y-m-d H:i:s'),
                    'descripcion'      => 'CONSULTA: SE RECIBIÓ RESPUESTA CON CÓDIGO NO EXITOSO',
                    'request'          => $requestData,
                    'response'         => $response,
                    'marca'            => $this->brand->id,
                    'marca_nombre'     => $this->brand->name,
                    'referencia'       => $this->data->reference,
                    'monto'            => $this->product->amount,
                    'uuid'             => $this->data->uuid,
                    'cdr'              => $this->identifier,
                ]);

                $processing->delete();

                $messages = $this->response((isset($response->respCode) ? $response->respCode : NULL), (isset($response->description) ? $response->description : NULL));

                return (object) [
                    'status' => FALSE,
                    'message' => $messages->message
                ];
            }
            /** END: Transacción fallida */

            /** Transacción exitosa */
            $end = date('Y-m-d H:i:s');

            /** Venta creada */
            $sale = Sale::create([
                'venta_concesionario' => $this->user->id,
                'venta_uuid'          => $this->data->uuid,
                'venta_producto'      => $this->product->id,
                'venta_proveedor'     => $this->provider->id,
                'venta_marca'         => $this->brand->id,
                'venta_referencia'    => $this->data->reference,
                'venta_importe'       => $this->product->amount,
                'venta_creada'        => $start,
                'venta_creada_por'    => request()->auth->user->id,
                'venta_procesada'     => $end,
                'venta_cdr'           => $this->identifier,
                'venta_cda'           => (isset($response->autorizacion) ? $response->autorizacion : NULL),

                'venta_extra' => [
                    'start'  => $this->user->config->balance_tae,
                    'amount' => $this->product->amount,
                    'end'  => ($this->user->config->balance_tae - $this->product->amount),
                ],

                'venta_codigo' => '00',
                'venta_status' => 1,
            ]);
            /** END: Venta creada */

            /** Transacción creada */
            $transaction = Transaction::create([
                'transaccion_concesionario'           => $this->user->id,
                'transaccion_sesion'                  => request()->auth->session->id,
                'transaccion_origen'                  => $this->user->id,
                'transaccion_generada'                => $start,
                'transaccion_procesada'               => $end,
                'transaccion_tipo'                    => 1,
                'transaccion_producto'                => 1,
                'transaccion_origen_saldo_inicial'    => $this->user->config->balance_tae,
                'transaccion_origen_saldo_monto'      => $this->product->amount,
                'transaccion_origen_saldo_comision'   => 0,
                'transaccion_origen_saldo_final'      => ($this->user->config->balance_tae - $this->product->amount),
                'transaccion_ip'                      => request()->ip(),
                'transaccion_referencia'              => $this->data->reference,
                'transaccion_cdr'                     => $this->identifier,
                'transaccion_uuid'                    => $this->data->uuid,
                'transaccion_cda'                     => (isset($response->autorizacion) ? $response->autorizacion : NULL),
                'transaccion_proveedor'               => $this->provider->id,
                'transaccion_respuesta'               => json_encode($response),
                'transaccion_venta'                   => $sale->id,
                'transaccion_request'                 => $requestData,
                'transaccion_response'                => $response,
                'transaccion_status'                  => 1,
            ]);
            /** END: Transacción creada */

            /** Saldo Actualizado */
            $userConfig = UserConfig::whereConfigConcesionario($this->user->id)->first();
            $userConfig->decrement('config_saldo', $this->product->amount);
            /** END: Saldo Actualizado */

            $processing->delete();

            return (object) [
                'status' => TRUE,
                'message' => 'Recarga exitosa',
                'data' => (object) [
                    'transaction' => $transaction,
                    'sale' => $sale,
                ]
            ];
            /** END: Transacción exitosa */
        }
        catch (\Exception $e)
        {
            TransactionFailed::create([
                'cliente'          => $this->user->id,
                'cliente_nombre'   => $this->user->name . ' (' . $this->user->business->name . ')',
                'proveedor'        => $this->provider->id,
                'proveedor_nombre' => $this->provider->name,
                'iniciada'         => $start,
                'finalizada'       => date('Y-m-d H:i:s'),
                'descripcion'      => 'CONSULTA: ERROR DE TIMEOUT, NO SE OBTUVO RESPUESTA',
                'request'          => $requestData,
                'response'         => $e->getMessage(),
                'marca'            => $this->brand->id,
                'marca_nombre'     => $this->brand->name,
                'referencia'       => $this->data->reference,
                'monto'            => $this->product->amount,
                'uuid'             => $this->data->uuid,
                'cdr'              => $this->identifier,
            ]);

            sleep(10);

            return $this->find($attemps);
        }
    }

    public function response($code, $description)
    {
        if (!is_null($description))
        {
            if (
                strpos(strtolower($description), 'se exceden saldoprepago') !== FALSE
            )
            {
                $response = (object) [
                    'code' => '98',
                    'message' => 'El Operador se encuentra fuera de servicio.'
                ];

                return $response;
            }

            if (
                strpos(strtolower($description), 'in saldo suficiente') !== FALSE
            )
            {
                $response = (object) [
                    'code' => '98',
                    'message' => 'El Operador se encuentra fuera de servicio.'
                ];

                return $response;
            }

            if (
                strpos($description, 'CORTE DEL DIA EN PROGRESO') !== FALSE
            )
            {
                $response = (object) [
                    'code' => '98',
                    'message' => 'El Operador se encuentra fuera de servicio.'
                ];

                return $response;
            }

            if (
                strpos(strtolower($description), 'ervicio no disponible') !== FALSE
            )
            {
                $response = (object) [
                    'code' => '98',
                    'message' => 'Error de interconexión con el operador.'
                ];

                return $response;
            }

            if (
                strpos(strtolower($description), 'telefono no valido para recarga') !== FALSE
            )
            {
                $response = (object) [
                    'code' => '04',
                    'message' => 'Número de teléfono con una suscripción o plan vigente.'
                ];

                return $response;
            }

            if (
                strpos(strtolower($description), 'telefono no valido') !== FALSE
            )
            {
                $response = (object) [
                    'code' => '03',
                    'message' => 'Número de teléfono no válido para este operador o en proceso de portabilidad.'
                ];

                return $response;
            }

            if (
                strpos($description, 'TELEFONO NO VALIDO') !== FALSE
            )
            {
                $response = (object) [
                    'code' => '03',
                    'message' => 'Número de teléfono no válido para este operador o en proceso de portabilidad.'
                ];

                return $response;
            }

            if (
                strpos($description, 'REVISAR OPERADOR') !== FALSE
            )
            {
                $response = (object) [
                    'code' => '03',
                    'message' => 'Número de teléfono no válido para este operador o en proceso de portabilidad.'
                ];

                return $response;
            }

            if (
                strpos($description, 'TELEFONO NO SUSCEPTIBLE DE ABONO') !== FALSE
            )
            {
                $response = (object) [
                    'code' => '03',
                    'message' => 'Número de teléfono no válido para este operador o en proceso de portabilidad.'
                ];

                return $response;
            }

            if (
                strpos(strtolower($description), 'ransaccion muy reciente a misma') !== FALSE
            )
            {
                $response = (object) [
                    'code' => '06',
                    'message' => 'Esta transacción ya fue realizada.'
                ];

                return $response;
            }

            if (
                strpos(strtolower($description), 'destino no existe') !== FALSE
            )
            {
                $response = (object) [
                    'code' => '02',
                    'message' => 'El formato del número no es válido.'
                ];

                return $response;
            }
        }

        $response = (object) [
            'code' => '05',
            'message' => 'La transacción fue rechazada por el operador por razones desconocidas.'
        ];

        return $response;
    }
}
