<?php

namespace App\Services\Providers;

use App\Models\Sale;
use App\Models\Transaction;
use App\Models\TransactionFailed;
use App\Models\TransactionProcessing;
use App\Models\UserConfig;
use Illuminate\Support\Facades\Log;
use SoapClient;

class SaldovirtualMovistar
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
        $id = date('YmdHis') . rand(100000, 999999);

        $cdr = Sale::whereVentaCdr($id)->first();

        while ($cdr)
        {
            $id = date('YmdHis') . rand(100000, 999999);

            $cdr = Sale::whereVentaCdr($id)->first();
        }

        return (object) [
            'status' => TRUE,
            'data' => $id,
        ];
    }

    public function balance()
    {
        $options = [
            'exceptions'         => true,
            'trace'              => 1,
            'keep_alive'         => false,
            'connection_timeout' => 90,
        ];

        $soapClient = new \SoapClient($this->config->url, $options);

        $requestData = '
            <SV>
                <DI>' . $this->config->id . '</DI>
                <PV>' . $this->config->user . '</PV>
                <PASSWORD>' . md5($this->config->key) . '</PASSWORD>
            </SV>
        ';

        try
        {
            $post = $soapClient->getSaldo($requestData);

            $result = simplexml_load_string($post);

            $balance = 0;

            if (empty($result->RESULTADO) or $result->RESULTADO !== 'EXITO')
            {
                return (object) ['status' => FALSE, 'message' => 'Ocurrió un error al consultar el saldo.', 'balance' => 0];
            }

            $balance = isset($result->SALDO) ? floatval($result->SALDO) : 0;

            return (object) ['status' => TRUE, 'balance' => $balance];
        }
        catch (\Exception $e)
        {
            Log::error('========== Error Consultando Saldo: Saldo Virtual Movistar ==========');
            Log::error($e);
            Log::error('========== Error Consultando Saldo: Saldo Virtual Movistar ==========');

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
            'connection_timeout' => 90,
        ];

        $soapClient = new SoapClient($this->config->url, $options);

        $requestData = '
            <SV>
                <DI>' . $this->config->id . '</DI>
                <PV>' . $this->config->user . '</PV>
                <PASSWORD>' . md5($this->config->key) . '</PASSWORD>
                <TELEFONO>' . $data->reference . '</TELEFONO>
                <CARRIER>' . $product->code . '</CARRIER>
                <MONTO>' . $product->amount . '</MONTO>
                <FC>' . $this->identifier . '</FC>
            </SV>
        ';

        try
        {
            $post = $soapClient->sendRecarga($requestData);

            $response = @simplexml_load_string($post);

            /** Transaccion con respuesta vacía */
            if (empty($post) or empty($response) or !isset($response->RESULTADO))
            {
                TransactionFailed::create([
                    'cliente'          => $this->user->id,
                    'cliente_nombre'   => $this->user->name . ' (' . $this->user->business->name . ')',
                    'proveedor'        => $this->provider->id,
                    'proveedor_nombre' => $this->provider->name,
                    'iniciada'         => $start,
                    'finalizada'       => date('Y-m-d H:i:s'),
                    'descripcion'      => 'RECARGA: NO SE EJECUTÓ LA TRANSACCIÓN',
                    'request'          => simplexml_load_string($requestData),
                    'response'         => NULL,
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

            /** Transacción en proceso o pendiente */
            if ($response->RESULTADO === 'PROCESANDO')
            {
                TransactionFailed::create([
                    'cliente'          => $this->user->id,
                    'cliente_nombre'   => $this->user->name . ' (' . $this->user->business->name . ')',
                    'proveedor'        => $this->provider->id,
                    'proveedor_nombre' => $this->provider->name,
                    'codigo'           => (isset($response->RESULTADO) ? $response->RESULTADO : NULL),
                    'error'            => (isset($response->MENSAJE) ? $response->MENSAJE : NULL),
                    'iniciada'         => $start,
                    'finalizada'       => date('Y-m-d H:i:s'),
                    'descripcion'      => 'RECARGA: SE RECIBIÓ RESPUESTA DE OPERACIÓN EN PROGRESO.',
                    'request'          => simplexml_load_string($requestData),
                    'response'         => $response,
                    'marca'            => $this->brand->id,
                    'marca_nombre'     => $this->brand->name,
                    'referencia'       => $this->data->reference,
                    'monto'            => $this->product->amount,
                    'uuid'             => $this->data->uuid,
                    'cdr'              => $this->identifier,
                ]);

                sleep(5);

                return $this->find();
            }
            /** END: Transacción en proceso o pendiente */

            /** Transacción fallida */
            if ($response->RESULTADO != 'EXITO')
            {
                TransactionFailed::create([
                    'cliente'          => $this->user->id,
                    'cliente_nombre'   => $this->user->name . ' (' . $this->user->business->name . ')',
                    'proveedor'        => $this->provider->id,
                    'proveedor_nombre' => $this->provider->name,
                    'codigo'           => (isset($response->RESULTADO) ? $response->RESULTADO : NULL),
                    'error'            => (isset($response->MENSAJE) ? $response->MENSAJE : NULL),
                    'iniciada'         => $start,
                    'finalizada'       => date('Y-m-d H:i:s'),
                    'descripcion'      => 'RECARGA: SE RECIBIÓ RESPUESTA CON CÓDIGO NO EXITOSO',
                    'request'          => simplexml_load_string($requestData),
                    'response'         => $response,
                    'marca'            => $this->brand->id,
                    'marca_nombre'     => $this->brand->name,
                    'referencia'       => $this->data->reference,
                    'monto'            => $this->product->amount,
                    'uuid'             => $this->data->uuid,
                    'cdr'              => $this->identifier,
                ]);

                $processing->delete();

                $messages = $this->response((isset($response->RESULTADO) ? $response->RESULTADO : NULL), (isset($response->MENSAJE) ? $response->MENSAJE : NULL));

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
                'venta_cda'           => (isset($response->AUTORIZACION) ? strval($response->AUTORIZACION) : NULL),

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
                'transaccion_cda'                     => (isset($response->AUTORIZACION) ? strval($response->AUTORIZACION) : NULL),
                'transaccion_proveedor'               => $this->provider->id,
                'transaccion_respuesta'               => json_encode($response),
                'transaccion_venta'                   => $sale->id,
                'transaccion_request'                 => simplexml_load_string($requestData),
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
                'request'          => simplexml_load_string($requestData),
                'response'         => $e->getMessage(),
                'marca'            => $this->brand->id,
                'marca_nombre'     => $this->brand->name,
                'referencia'       => $this->data->reference,
                'monto'            => $this->product->amount,
                'uuid'             => $this->data->uuid,
                'cdr'              => $this->identifier,
            ]);

            sleep(5);

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

        if ($attemps > 24)
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

        $requestData = '
            <SV>
                <DI>' . $this->config->id . '</DI>
                <PV>' . $this->config->user . '</PV>
                <PASSWORD>' . md5($this->config->key) . '</PASSWORD>
                <FC>' . $this->identifier . '</FC>
            </SV>
        ';

        try
        {
            $post = $soapClient->checkRecarga($requestData);

            $response = @simplexml_load_string($post);

            /** Transaccion con respuesta vacía */
            if (empty($post) or empty($response) or !isset($response->RESULTADO))
            {
                TransactionFailed::create([
                    'cliente'          => $this->user->id,
                    'cliente_nombre'   => $this->user->name . ' (' . $this->user->business->name . ')',
                    'proveedor'        => $this->provider->id,
                    'proveedor_nombre' => $this->provider->name,
                    'iniciada'         => $start,
                    'finalizada'       => date('Y-m-d H:i:s'),
                    'descripcion'      => 'CONSULTA: NO SE EJECUTÓ LA TRANSACCIÓN',
                    'request'          => simplexml_load_string($requestData),
                    'response'         => NULL,
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

            $response = $post->BuscarRecargaResult;

            /** Transacción en proceso o pendiente */
            if ($response->RESULTADO === 'PROCESANDO')
            {
                TransactionFailed::create([
                    'cliente'          => $this->user->id,
                    'cliente_nombre'   => $this->user->name . ' (' . $this->user->business->name . ')',
                    'proveedor'        => $this->provider->id,
                    'proveedor_nombre' => $this->provider->name,
                    'codigo'           => (isset($response->RESULTADO) ? $response->RESULTADO : NULL),
                    'error'            => (isset($response->MENSAJE) ? $response->MENSAJE : NULL),
                    'iniciada'         => $start,
                    'finalizada'       => date('Y-m-d H:i:s'),
                    'descripcion'      => 'CONSULTA: SE RECIBIÓ RESPUESTA DE OPERACIÓN EN PROGRESO.',
                    'request'          => simplexml_load_string($requestData),
                    'response'         => $response,
                    'marca'            => $this->brand->id,
                    'marca_nombre'     => $this->brand->name,
                    'referencia'       => $this->data->reference,
                    'monto'            => $this->product->amount,
                    'uuid'             => $this->data->uuid,
                    'cdr'              => $this->identifier,
                ]);

                sleep(5);

                return $this->find($attemps);
            }
            /** END: Transacción en proceso o pendiente */

            /** Transacción fallida */
            if ($response->RESULTADO != 'EXITO')
            {
                TransactionFailed::create([
                    'cliente'          => $this->user->id,
                    'cliente_nombre'   => $this->user->name . ' (' . $this->user->business->name . ')',
                    'proveedor'        => $this->provider->id,
                    'proveedor_nombre' => $this->provider->name,
                    'codigo'           => (isset($response->RESULTADO) ? $response->RESULTADO : NULL),
                    'error'            => (isset($response->MENSAJE) ? $response->MENSAJE : NULL),
                    'iniciada'         => $start,
                    'finalizada'       => date('Y-m-d H:i:s'),
                    'descripcion'      => 'CONSULTA: SE RECIBIÓ RESPUESTA CON CÓDIGO NO EXITOSO',
                    'request'          => simplexml_load_string($requestData),
                    'response'         => $response,
                    'marca'            => $this->brand->id,
                    'marca_nombre'     => $this->brand->name,
                    'referencia'       => $this->data->reference,
                    'monto'            => $this->product->amount,
                    'uuid'             => $this->data->uuid,
                    'cdr'              => $this->identifier,
                ]);

                $processing->delete();

                $messages = $this->response((isset($response->RESULTADO) ? $response->RESULTADO : NULL), (isset($response->MENSAJE) ? $response->MENSAJE : NULL));

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
                'venta_cda'           => (isset($response->AUTORIZACION) ? strval($response->AUTORIZACION) : NULL),

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
                'transaccion_cda'                     => (isset($response->AUTORIZACION) ? strval($response->AUTORIZACION) : NULL),
                'transaccion_proveedor'               => $this->provider->id,
                'transaccion_respuesta'               => json_encode($response),
                'transaccion_venta'                   => $sale->id,
                'transaccion_request'                 => simplexml_load_string($requestData),
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
                'request'          => simplexml_load_string($requestData),
                'response'         => $e->getMessage(),
                'marca'            => $this->brand->id,
                'marca_nombre'     => $this->brand->name,
                'referencia'       => $this->data->reference,
                'monto'            => $this->product->amount,
                'uuid'             => $this->data->uuid,
                'cdr'              => $this->identifier,
            ]);

            sleep(5);

            return $this->find($attemps);
        }
    }

    public function response($code, $description)
    {
        if (!is_null($description))
        {
            if (
                strpos(strtolower($description), 'no existe el monto') !== FALSE or
                strpos(strtolower($description), 'no existe el sku') !== FALSE or
                strpos(strtolower($description), 'error en monto') !== FALSE or
                strpos(strtolower($description), 'datos invalidos') !== FALSE or
                strpos(strtolower($description), 'no encontrado') !== FALSE or
                strpos(strtolower($description), 'reservado') !== FALSE
            )
            {
                $response = (object) [
                    'code' => '01',
                    'message' => 'Este producto no es válido.'
                ];

                return $response;
            }

            if (
                strpos(strtolower($description), 'no cuenta con saldo') !== FALSE or
                strpos(strtolower($description), 'sin respuesta del operador') !== FALSE
            )
            {
                $response = (object) [
                    'code' => '98',
                    'message' => 'El Operador se encuentra fuera de servicio.'
                ];

                return $response;
            }

            if (
                strpos(strtolower($description), 'ip se encuentra bloqueada') !== FALSE or
                strpos(strtolower($description), 'no puede utilizar el servicio de ws') !== FALSE or
                strpos(strtolower($description), 'xml invalido') !== FALSE or
                strpos(strtolower($description), 'no existe conexion') !== FALSE or
                strpos(strtolower($description), 'mantenimiento en curso') !== FALSE or
                strpos(strtolower($description), 'error en credenciales') !== FALSE or
                strpos(strtolower($description), 'no tiene habilitado el carrier') !== FALSE or
                strpos(strtolower($description), 'su ip no se encuentra registrada') !== FALSE or
                strpos(strtolower($description), 'operacion no permitida') !== FALSE or
                strpos(strtolower($description), 'no cumple con los parametros') !== FALSE
            )
            {
                $response = (object) [
                    'code' => '98',
                    'message' => 'Error de interconexión con el operador.'
                ];

                return $response;
            }

            if (
                strpos(strtolower($description), 'numero telefonico invalido') !== FALSE or
                strpos(strtolower($description), 'revise el id del carrier') !== FALSE or
                strpos(strtolower($description), 'no cuenta con permisos con el carrier') !== FALSE or
                strpos(strtolower($description), 'pendiente de activacion') !== FALSE
            )
            {
                $response = (object) [
                    'code' => '03',
                    'message' => 'Número de teléfono no válido para este operador o en proceso de portabilidad.'
                ];

                return $response;
            }

            if (
                strpos(strtolower($description), 'error al procesar venta') !== FALSE
            )
            {
                $response = (object) [
                    'code' => '05',
                    'message' => 'La transacción fue rechazada por el operador por razones desconocidas.'
                ];

                return $response;
            }

            if (
                strpos(strtolower($description), 'numero ya recargado') !== FALSE or
                strpos(strtolower($description), 'espere 5 minutos') !== FALSE
            )
            {
                $response = (object) [
                    'code' => '06',
                    'message' => 'Esta transacción ya fue realizada.'
                ];

                return $response;
            }

            if (
                strpos(strtolower($description), 'temporalmente') !== FALSE
            )
            {
                $response = (object) [
                    'code' => '13',
                    'message' => 'Producto no disponible temporalmente.'
                ];

                return $response;
            }

            if (
                strpos(strtolower($description), 'operacion en progreso') !== FALSE or
                strpos(strtolower($description), 'reintente nuevamente') !== FALSE or
                strpos(strtolower($description), 'intenta nuevamente') !== FALSE
            )
            {
                $response = (object) [
                    'code' => '50',
                    'message' => 'Transacción en proceso.'
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
