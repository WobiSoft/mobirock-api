<?php

namespace App\Services\Providers;

use App\Models\ProviderCode;
use App\Models\Sale;
use App\Models\Transaction;
use App\Models\TransactionFailed;
use App\Models\TransactionProcessing;
use App\Models\UserConfig;
use Illuminate\Support\Facades\Log;
use SoapClient;

class OperadoranvTelcel
{
    public $data;
    public $user;
    public $userConfig;
    public $provider;
    public $config;
    public $brand;
    public $product;

    public function set($provider, $config)
    {
        $this->provider = $provider;
        $this->config = $config;
    }

    public function id()
    {
        $id = date('ymdHi') . rand(10, 99);

        $transaction = Sale::whereVentaCdr($id)->first();

        while ($transaction)
        {
            $id = date('ymdHi') . rand(10, 99);

            $transaction = Sale::whereVentaCdr($id)->first();
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
            'connection_timeout' => 10,
        ];

        $soapClient = new \SoapClient($this->config->url, $options);

        $requestData = [
            'claveCanal'  => $this->config->user,
            'passCanal' => $this->config->key,
        ];

        try
        {
            $post = $soapClient->Saldo($requestData);

            $balance = ($post->saldo * ($this->provider->proveedor_saldo_negativo ? -1 : 1));

            return (object) ['status' => TRUE, 'balance' => $balance];
        }
        catch (\Exception $e)
        {
            Log::error('========== Error Consultando Saldo: Operadora NV Telcel ==========');
            Log::error($e);
            Log::error('========== Error Consultando Saldo: Operadora NV Telcel ==========');

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
            'claveCanal' => $this->config->user,
            'passCanal'  => $this->config->key,
            'id'         => $this->identifier,
            'terminal'   => 'PRODUCCION',
            'producto'   => $this->product->code,
            'destino'    => $this->data->reference,
            'monto'      => $this->product->amount
        ];

        try
        {
            if (config('app.env') === 'local')
            {
                $post = (object) [
                    'rcode' => '00',
                    'confirma' => rand(100000000, 999999999),
                ];
            }
            else
            {
                $post = $soapClient->Venta($requestData);
            }

            /** Transaccion con respuesta vacía */
            if (!isset($post->rcode))
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

            $response = $post;

            /** Transacción en proceso o pendiente */
            if (in_array($response->rcode, ['88', '89', 88, 89]))
            {
                TransactionFailed::create([
                    'cliente'          => $this->user->id,
                    'cliente_nombre'   => $this->user->name . ' (' . $this->user->business->name . ')',
                    'proveedor'        => $this->provider->id,
                    'proveedor_nombre' => $this->provider->name,
                    'codigo'           => (isset($response->rcode) ? $response->rcode : NULL),
                    'error'            => $this->message(($response->rcode ? $response->rcode : NULL)),
                    'iniciada'         => $start,
                    'finalizada'       => date('Y-m-d H:i:s'),
                    'descripcion'      => 'RECARGA: SE RECIBIÓ RESPUESTA DE ' . ($response->rcode == 88 ? 'OPERACIÓN EN PROGRESO.' : 'OPERACIÓN NO ENCONTRADA.') . '.',
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
            if ($response->rcode !== '00')
            {
                TransactionFailed::create([
                    'cliente'          => $this->user->id,
                    'cliente_nombre'   => $this->user->name . ' (' . $this->user->business->name . ')',
                    'proveedor'        => $this->provider->id,
                    'proveedor_nombre' => $this->provider->name,
                    'codigo'           => (isset($response->rcode) ? $response->rcode : NULL),
                    'error'            => $this->message(($response->rcode ? $response->rcode : NULL)),
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

                $messages = $this->response((isset($response->rcode) ? $response->rcode : NULL));

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
                'venta_cda'           => (isset($response->confirma) ? $response->confirma : NULL),

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
                'transaccion_cda'                     => (isset($response->confirma) ? $response->confirma : NULL),
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
            'claveCanal' => $this->config->user,
            'passCanal'  => $this->config->key,
            'id'         => $this->identifier,
            'terminal'   => 'PRODUCCION',
            'producto'   => $this->product->code,
            'destino'    => $this->data->reference,
            'monto'      => $this->product->amount
        ];

        try
        {
            $post = $soapClient->StatusVenta($requestData);

            /** Transaccion con respuesta vacía */
            if (!isset($post->rcode))
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

            $response = $post;

            /** Transacción en proceso o pendiente */
            if (in_array($response->rcode, ['88', '89', 88, 89]))
            {
                TransactionFailed::create([
                    'cliente'          => $this->user->id,
                    'cliente_nombre'   => $this->user->name . ' (' . $this->user->business->name . ')',
                    'proveedor'        => $this->provider->id,
                    'proveedor_nombre' => $this->provider->name,
                    'codigo'           => (isset($response->rcode) ? $response->rcode : NULL),
                    'error'            => $this->message(($response->rcode ? $response->rcode : NULL)),
                    'iniciada'         => $start,
                    'finalizada'       => date('Y-m-d H:i:s'),
                    'descripcion'      => 'CONSULTA: SE RECIBIÓ RESPUESTA DE ' . ($response->rcode == 88 ? 'OPERACIÓN EN PROGRESO.' : 'OPERACIÓN NO ENCONTRADA.') . '.',
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
            if ($response->rcode !== '00')
            {
                TransactionFailed::create([
                    'cliente'          => $this->user->id,
                    'cliente_nombre'   => $this->user->name . ' (' . $this->user->business->name . ')',
                    'proveedor'        => $this->provider->id,
                    'proveedor_nombre' => $this->provider->name,
                    'codigo'           => (isset($response->rcode) ? $response->rcode : NULL),
                    'error'            => $this->message(($response->rcode ? $response->rcode : NULL)),
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
                'venta_cda'           => (isset($response->confirma) ? $response->confirma : NULL),

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
                'transaccion_cda'                     => (isset($response->confirma) ? $response->confirma : NULL),
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

    public function response($code)
    {
        $response = (object) [
            'code' => '99',
            'message' => 'Error desconocido.'
        ];

        switch ($code)
        {
                //Clave secreta invalida.
            case '02':
                //Cambio externo en los datos.
            case '09':
                //La nueva clave es identica a la anterior.
            case '10':
                //No se permite la venta a si mismo.
            case '11':
                //No se permite la transferencia a si mismo.
            case '12':
                //Ningun cargador ha autorizado.
            case '17':
                //Ningun cargador ha respondido la llamada.
            case '18':
                //No tiene cargadores registrados.
            case '19':
                //Telefono en uso.
            case '23':
                //Telefono Destino en uso.
            case '24':
                //Transaccion fuera de horario.
            case '30':
                //No se puede revertir este producto.
            case '32':
                //Comision invalida para el producto seleccionado, contacte al distribuidor.
            case '36':
                //Producto seleccionado no tiene comision, contacte al distribuidor.
            case '37':
                //Demasiados documentos.
            case '38':
                //Ya no puede hacer mas peticiones de este tipo.
            case '54':
                //Retener tarjeta del cliente.
            case '60':
                //Por favor llamar a la emisora de la tarjeta.
            case '61':
                //Código de seguridad inválido.
            case '62':
                //Operacion en progreso.
            case '88':
                //Operacion no encontrada.
            case '89':
                //El mensaje no pudo ser recibido en su totalidad.
            case '90':
                //El mensaje no tiene todos los datos necesarios.
            case '91':
                //El mensaje contiene informacion de sobra.
            case '92':
                //No se puede enviar la respuesta.
            case '93':
                //Limite de intentos de PIN erroneo; usuario ha sido bloqueado.
            case '102':
                //Datos no coinciden con lo indicado en la petición.
            case '104':
                //La pérdida ya ha sido reportada.
            case '105':
                //Punto de venta no tiene Region Telcel configurada.
            case '2029':
                //Respuesta de concurso ACERTADA.
            case '9900':
                //Respuesta de concurso ERRONEA.
            case '9901':
                //Chiste enviado.
            case '9995':
                //Tip de Amor enviado.
            case '9996':
                //Tarot enviado.
            case '9997':
                //Aviso enviado al proveedor.
            case '9999':
                $response->code = '98';
                $response->message = 'Error de interconexión con el operador.';
                break;

                //Saldo insuficiente, intente por un monto menor.
            case '05':
                //Limite de transferencia excedido.
            case '16':
                //El maximo de compras diarias ha sido realizado.
            case '27':
                //Limite de credito excedido.
            case '28':
                //Su saldo ha expirado.
            case '103':
                $response->code = '98';
                $response->message = 'El Operador se encuentra fuera de servicio.';
                break;

                //Usuario no esta registrado.
            case '01':
                //Usuario destino no esta registrado.
            case '03':
                //Usuario desconocido.
            case '06':
                //No se puede registrar al usuario.
            case '07':
                //Usuario ya esta registrado.
            case '08':
                //Usuario esta bloqueado.
            case '15':
                //No ha pasado el periodo minimo de activacion.
            case '52':
                //El destino no ha pasado el periodo minimo de activacion.
            case '53':
                $response->code = '03';
                $response->message = 'Número de teléfono no válido para este operador o en proceso de portabilidad.';
                break;

                //Monto invalido.
            case '04':
                $response->code = '01';
                $response->message = 'Este producto no es válido.';
                break;

                //No se puede realizar la recarga.
            case '13':
                //Operacion no permitida.
            case '14':
                //El servidor de prepago no responde.
            case '20':
                //El servidor de prepago no esta disponible.
            case '21':
                //El producto solicitado esta agotado.
            case '29':
                //Codigo de proveedor invalido.
            case '31':
                //Producto no asignado.
            case '33':
                //Producto temporalmente no disponible.
            case '34':
                //Servicio no disponible.
            case '35':
                //Error interno. Favor de notificar al operador.
            case '50':
                //Base de datos no disponible.
            case '51':
                //Producto no disponible por el momento.
            case '55':
                //El cliente tiene compras demasiado recientes.
            case '56':
                //Operación denegada.
            case '63':
                //Terminal inválida.
            case '64':
                //Operacion no fue procesada.
            case '87':
                //Por favor intente de nuevo en 5 minutos.
            case '94':
                //Mensaje invalido.
            case '99':
                $response->code = '05';
                $response->message = 'La transacción fue rechazada por el operador por razones desconocidas.';
                break;

                //Destino no es un telefono de prepago.
            case '22':
                //El usuario destino esta bloqueado.
            case '25':
                //Tiene una suscripción vigente, espere 24h.
            case '40':
                //Cuenta con una suscripicion vigente, espere 24hrs.
            case '95':
                $response->code = '04';
                $response->message = 'Número de teléfono con una suscripción o plan vigente.';
                break;

                //Venta reciente de este punto a ese destino.
            case '26':
                $response->code = '06';
                $response->message = 'Esta transacción ya fue realizada.';
                break;

                //Petición duplicada.
            case '39':
                $response->code = '06';
                $response->message = 'Esta transacción ya fue realizada.';
                break;

                //Operacion fue revertida.
            case '86':
                $response->code = '98';
                $response->message = 'Error de interconexión con el operador.';
                break;
        }

        return $response;
    }

    public function message($code)
    {
        $providerCode = ProviderCode::select('mensaje')
            ->whereCodigo($code)
            ->whereProveedor($this->provider->id)
            ->first();

        return $providerCode->message;
    }
}
