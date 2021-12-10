<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    public $timestamps = false;
    protected $table = 'transacciones';
    protected $primaryKey = 'transaccion_id';

    protected $guarded = [];

    protected $appends = [
        'id',
        'payment_id',
        'user_id',
        'session_id',
        'sender_id',
        'sender',
        'receiver_id',
        'receiver',
        'generated_at',
        'processed_at',
        'type',
        'product',
        'fee_percentage',
        'ip',
        'reference',
        'identifier',
        'uuid',
        'authorization_code',
        'provider_id',
        'answer',
        'sale_id',
        'purchase_id',
        'extraction_id',
        'extraction_reason',
        'comments',
        'request',
        'response',
        'messages',
        'sms',
        'status',
    ];

    protected $hidden = [
        'transaccion_id',                     // BIGINT(20) NOT NULL AUTO_INCREMENT,
        'transaccion_pago',                   // BIGINT(20) NULL DEFAULT NULL COMMENT 'ID del Pago asignado a esta Transaccion (Opcional)',
        'transaccion_concesionario',          // BIGINT(20) NULL DEFAULT NULL COMMENT 'ID del Concesionario que realizó la Transacción (Hijo o Padre)',
        'transaccion_sesion',                 // BIGINT(20) NULL DEFAULT NULL COMMENT 'ID de la Sesión con la que se realizó la Transacción',
        'transaccion_origen',                 // BIGINT(20) NULL DEFAULT NULL COMMENT 'ID Usuario que envia el Saldo',
        'transaccion_destino',                // BIGINT(20) NULL DEFAULT NULL COMMENT 'ID Usuario que recibe el Saldo (Solo para transaccion_tipo 2 y 3)',
        'transaccion_generada',               // TIMESTAMP NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha en que se crea el Registro de la Transacción',
        'transaccion_procesada',              // DATETIME NULL DEFAULT NULL COMMENT 'Fecha en que se realiza la Transacción (ej. Fecha y Hora de respuesta del Proveedor)',
        'transaccion_tipo',                   // TINYINT(1) NULL DEFAULT NULL COMMENT '1 = Venta (Al Cliente Final), 2 = Compra (De un Usuario a Otro), 3 = Reverso (De un Usuario a Otro), 4 = Comisión por Descuento, 5 = Comisión por Pago de Servicios, 6 = Comisión por Pago de Servicios en Efectivo, 7 = Conversión de Comision TAE, 8 = Conversión de Comisión PDS',
        'transaccion_producto',               // TINYINT(1) NULL DEFAULT '1' COMMENT '1 = TAE, 2 = PDS',
        'transaccion_comision',               // DECIMAL(20,2) NULL DEFAULT '0.00' COMMENT 'Porcentaje de Comisión del Usuario Destino al momento de realizar la Transacción',
        'transaccion_origen_saldo_inicial',   // DECIMAL(20,2) NULL DEFAULT '0.00',
        'transaccion_origen_saldo_monto',     // DECIMAL(20,2) NULL DEFAULT '0.00',
        'transaccion_origen_saldo_comision',  // DECIMAL(20,2) NULL DEFAULT '0.00',
        'transaccion_origen_saldo_final',     // DECIMAL(20,2) NULL DEFAULT '0.00',
        'transaccion_destino_saldo_inicial',  // DECIMAL(20,2) NULL DEFAULT '0.00',
        'transaccion_destino_saldo_monto',    // DECIMAL(20,2) NULL DEFAULT '0.00',
        'transaccion_destino_saldo_comision', // DECIMAL(20,2) NULL DEFAULT '0.00',
        'transaccion_destino_saldo_final',    // DECIMAL(20,2) NULL DEFAULT '0.00',
        'transaccion_ip',                     // VARCHAR(255) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'transaccion_referencia',             // VARCHAR(100) NULL DEFAULT NULL COMMENT 'Número Telefónico, Número de Servicio, etc.' COLLATE 'latin1_swedish_ci',
        'transaccion_cdr',                    // VARCHAR(36) NULL DEFAULT NULL COMMENT 'Código de Rastreo (Generado en REDPrepaid)' COLLATE 'latin1_swedish_ci',
        'transaccion_uuid',                   // VARCHAR(36) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'transaccion_cda',                    // VARCHAR(255) NULL DEFAULT NULL COMMENT 'Código de Autorización (Regresado por el Proveedor)' COLLATE 'latin1_swedish_ci',
        'transaccion_proveedor',              // BIGINT(20) NULL DEFAULT NULL COMMENT 'ID del Proveedor que proceso la Transacción',
        'transaccion_respuesta',              // TEXT NULL DEFAULT NULL COMMENT 'Respuesta del WebService del Proveedor' COLLATE 'latin1_swedish_ci',
        'transaccion_venta',                  // BIGINT(20) NULL DEFAULT NULL COMMENT 'ID de la Venta (Opcional)',
        'transaccion_compra',                 // BIGINT(20) NULL DEFAULT NULL COMMENT 'ID de la Compra (Opcional)',
        'transaccion_reverso',                // BIGINT(20) NULL DEFAULT NULL COMMENT 'ID del Reverso (Opcional)',
        'transaccion_reverso_razon',          // TEXT NULL DEFAULT NULL COMMENT 'Razón del Reverso' COLLATE 'latin1_swedish_ci',
        'transaccion_observaciones',          // TEXT NULL DEFAULT NULL COMMENT 'Observaciones de la Transaccion' COLLATE 'latin1_swedish_ci',
        'transaccion_request',                // LONGTEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'transaccion_response',               // LONGTEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'transaccion_mensajes',               // LONGTEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'transaccion_sms',                    // TINYINT(1) UNSIGNED NULL DEFAULT '0' COMMENT '0 = No por SMS, 1 = Por SMS',
        'transaccion_status',                 // TINYINT(1) NOT NULL DEFAULT '2' COMMENT '0 = Fallida, 1 = Exitosa, 2 = En Proceso',
    ];

    protected $casts = [
        'transaccion_pago' => 'integer',
        'transaccion_concesionario' => 'integer',
        'transaccion_sesion' => 'integer',
        'transaccion_origen' => 'integer',
        'transaccion_proveedor' => 'integer',
        'transaccion_venta' => 'integer',
        'transaccion_compra' => 'integer',
        'transaccion_reverso' => 'integer',
        'transaccion_destino' => 'integer',
        'transaccion_generada' => 'datetime',
        'transaccion_procesada' => 'datetime',
        'transaccion_comision' => 'float',
        'transaccion_origen_saldo_inicial' => 'float',
        'transaccion_origen_saldo_monto' => 'float',
        'transaccion_origen_saldo_comision' => 'float',
        'transaccion_origen_saldo_final' => 'float',
        'transaccion_destino_saldo_inicial' => 'float',
        'transaccion_destino_saldo_monto' => 'float',
        'transaccion_destino_saldo_comision' => 'float',
        'transaccion_destino_saldo_final' => 'float',
        'transaccion_sms' => 'boolean',
        'transaccion_respuesta' => 'encrypted',
        'transaccion_request' => 'json',
        'transaccion_response' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'transaccion_concesionario');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'transaccion_pago');
    }

    public function session()
    {
        return $this->belongsTo(UserSession::class, 'transaccion_sesion');
    }

    public function senderInfo()
    {
        return $this->belongsTo(User::class, 'transaccion_origen');
    }

    public function receiverInfo()
    {
        return $this->belongsTo(User::class, 'transaccion_destino');
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'transaccion_proveedor');
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'transaccion_venta');
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'transaccion_compra');
    }

    public function extraction()
    {
        return $this->belongsTo(Extraction::class, 'transaccion_reverso');
    }

    public function getIdAttribute()
    {
        return $this->transaccion_id;
    }

    public function getPaymentIdAttribute()
    {
        return $this->transaccion_pago;
    }

    public function getUserIdAttribute()
    {
        return $this->transaccion_concesionario;
    }

    public function getSessionIdAttribute()
    {
        return $this->transaccion_sesion;
    }

    public function getSenderIdAttribute()
    {
        return $this->transaccion_origen;
    }

    public function getReceiverIdAttribute()
    {
        return $this->transaccion_destino;
    }

    public function getGeneratedAtAttribute()
    {
        return $this->transaccion_generada;
    }

    public function getProcessedAtAttribute()
    {
        return $this->transaccion_procesada;
    }

    public function getTypeAttribute()
    {
        return (object) [
            'id' => intval($this->transaccion_tipo),
            'name' => ($this->transaccion_tipo == 1 ? 'Venta (Al Cliente Final)' : ($this->transaccion_tipo == 2 ? 'Compra (De un Usuario a Otro)' : ($this->transaccion_tipo == 3 ? 'Reverso (De un Usuario a Otro)' : ($this->transaccion_tipo == 4 ? 'Comisión por Descuento' : ($this->transaccion_tipo == 5 ? 'Comisión por Pago de Servicios' : ($this->transaccion_tipo == 6 ? 'Comisión por Pago de Servicios en Efectivo' : ($this->transaccion_tipo == 7 ? 'Conversión de Comision TAE' : ($this->transaccion_tipo == 8 ? 'Conversión de Comisión PDS' : 'Inválida'))))))))
        ];
    }

    public function getProductAttribute()
    {
        return (object) [
            'id' => intval($this->transaccion_producto),
            'name' => ($this->transaccion_producto == 1 ? 'Tiempo Aire Electrónico' : ($this->transaccion_producto == 2 ? 'Pago de Servicios' : 'Inválido'))
        ];
    }

    public function getFeePercentageAttribute()
    {
        return $this->transaccion_comision;
    }

    public function getSenderAttribute()
    {
        return (object) [
            'start' => $this->transaccion_origen_saldo_inicial,
            'amount' => $this->transaccion_origen_saldo_monto,
            'fee' => $this->transaccion_origen_saldo_comision,
            'final' => $this->transaccion_origen_saldo_final,
        ];
    }

    public function getReceiverAttribute()
    {
        return (object) [
            'start' => $this->transaccion_destino_saldo_inicial,
            'amount' => $this->transaccion_destino_saldo_monto,
            'fee' => $this->transaccion_destino_saldo_comision,
            'final' => $this->transaccion_destino_saldo_final,
        ];
    }

    public function getIpAttribute()
    {
        return $this->transaccion_ip;
    }

    public function getReferenceAttribute()
    {
        return $this->transaccion_referencia;
    }

    public function getIdentifierAttribute()
    {
        return $this->transaccion_cdr;
    }

    public function getUuidAttribute()
    {
        return $this->transaccion_uuid;
    }

    public function getAuthorizationCodeAttribute()
    {
        return $this->transaccion_cda;
    }

    public function getProviderIdAttribute()
    {
        return $this->transaccion_proveedor;
    }

    public function getAnswerAttribute()
    {
        return $this->transaccion_respuesta;
    }

    public function getSaleIdAttribute()
    {
        return $this->transaccion_venta;
    }

    public function getPurchaseIdAttribute()
    {
        return $this->transaccion_compra;
    }

    public function getExtractionIdAttribute()
    {
        return $this->transaccion_reverso;
    }

    public function getExtractionReasonAttribute()
    {
        return $this->transaccion_reverso_razon;
    }

    public function getCommentsAttribute()
    {
        return $this->transaccion_observaciones;
    }

    public function getRequestAttribute()
    {
        return $this->transaccion_request;
    }

    public function getResponseAttribute()
    {
        return $this->transaccion_response;
    }

    public function getMessagesAttribute()
    {
        return $this->transaccion_mensajes;
    }

    public function getSmsAttribute()
    {
        return $this->transaccion_sms;
    }

    public function getStatusAttribute()
    {
        return (object) [
            'id' => intval($this->transaccion_status),
            'name' => ($this->transaccion_status == 0 ? 'Fallida' : ($this->transaccion_status == 1 ? 'Exitosa' : ($this->transaccion_status == 2 ? 'En Proceso' : 'Inválido')))
        ];
    }
}
