<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    public $timestamps = false;
    protected $table = 'concesionarios_ventas';
    protected $primaryKey = 'venta_id';

    protected $guarded = [];

    protected $appends = [
        'id',
        'user_id',
        'uuid',
        'product_id',
        'provider_id',
        'brand_id',
        'reference',
        'amount',
        'created_at',
        'created_by_id',
        'processed_at',
        'identifier',
        'authorization_code',
        'extra',
        'comments',
        'code',
        'debug',
        'status',
    ];

    protected $hidden = [
        'venta_id',            // BIGINT(20) NOT NULL AUTO_INCREMENT,
        'venta_concesionario', // TEXT NULL DEFAULT NULL COMMENT 'ID del Concesionario al que pertenece la Venta' COLLATE 'latin1_swedish_ci',
        'venta_uuid',          // TEXT NOT NULL COMMENT 'Identificador Único para la Aplicación' COLLATE 'latin1_swedish_ci',
        'venta_producto',      // BIGINT(20) NULL DEFAULT NULL COMMENT 'ID del Producto al que va ligada esta Venta',
        'venta_proveedor',     // BIGINT(20) NULL DEFAULT NULL COMMENT 'ID del Proveedor al que va ligada la Venta',
        'venta_marca',         // BIGINT(20) NULL DEFAULT NULL COMMENT 'ID de la Marca de la Venta',
        'venta_referencia',    // TEXT NOT NULL COMMENT 'Número de Celular, de Recibo, etc.' COLLATE 'latin1_swedish_ci',
        'venta_importe',       // DECIMAL(20,2) NOT NULL,
        'venta_creada',        // TIMESTAMP NOT NULL DEFAULT current_timestamp(),
        'venta_creada_por',    // BIGINT(20) NULL DEFAULT NULL COMMENT 'ID del Concesionario que la creó',
        'venta_procesada',     // DATETIME NULL DEFAULT NULL COMMENT 'Fecha y Hora en que se procesó la Venta',
        'venta_cdr',           // TEXT NULL DEFAULT NULL COMMENT 'Identificador ligado con el Proveedor, solicitado por el' COLLATE 'latin1_swedish_ci',
        'venta_cda',           // TEXT NULL DEFAULT NULL COMMENT 'Código de Autorización regresado por el Proveedor' COLLATE 'latin1_swedish_ci',
        'venta_extra',         // TEXT NULL DEFAULT NULL COMMENT 'En caso de Regresar un Dato extra para servicios electrónicos' COLLATE 'latin1_swedish_ci',
        'venta_observaciones', // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'venta_codigo',        // TEXT NULL DEFAULT NULL COMMENT 'Código de Respuesta para usuario final' COLLATE 'latin1_swedish_ci',
        'venta_debug',         // TEXT NULL DEFAULT NULL COMMENT 'Los errores por Proveedor en Formato JSON' COLLATE 'latin1_swedish_ci',
        'venta_status',        // TINYINT(1) NULL DEFAULT NULL COMMENT '0 = Fallida, 1 = Exitosa, 2 = En Proceso',
    ];

    protected $casts = [
        'venta_concesionario' => 'integer',
        'venta_producto'      => 'integer',
        'venta_proveedor'     => 'integer',
        'venta_marca'         => 'integer',
        'venta_creada_por'    => 'integer',
        'venta_importe'       => 'float',
        'venta_extra'         => 'json',
        'venta_status'        => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'venta_concesionario');
    }

    public function product()
    {
        return $this->belongsTo(ProviderProduct::class, 'venta_producto');
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'venta_proveedor');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'venta_marca');
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'venta_creada_por');
    }

    public function getIdAttribute()
    {
        return $this->venta_id;
    }

    public function getUserIdAttribute()
    {
        return $this->venta_concesionario;
    }

    public function getUuidAttribute()
    {
        return $this->venta_uuid;
    }

    public function getProductIdAttribute()
    {
        return $this->venta_producto;
    }

    public function getProviderIdAttribute()
    {
        return $this->venta_proveedor;
    }

    public function getBrandIdAttribute()
    {
        return $this->venta_marca;
    }

    public function getReferenceAttribute()
    {
        return $this->venta_referencia;
    }

    public function getAmountAttribute()
    {
        return $this->venta_importe;
    }

    public function getCreatedAtAttribute()
    {
        return $this->venta_creada;
    }

    public function getCreatedByIdAttribute()
    {
        return $this->venta_creada_por;
    }

    public function getProcessedAtAttribute()
    {
        return $this->venta_procesada;
    }

    public function getIdentifierAttribute()
    {
        return $this->venta_cdr;
    }

    public function getAuthorizationCodeAttribute()
    {
        return $this->venta_cda;
    }

    public function getExtraAttribute()
    {
        return $this->venta_extra;
    }

    public function getCommentsAttribute()
    {
        return $this->venta_observaciones;
    }

    public function getCodeAttribute()
    {
        return $this->venta_codigo;
    }

    public function getDebugAttribute()
    {
        return $this->venta_debug;
    }

    public function getStatusAttribute()
    {
        return (object) [
            'id' => intval($this->venta_status),
            'name' => ($this->venta_status == 0 ? 'Fallida' : ($this->venta_status == 1 ? 'Exitosa' : ($this->venta_status == 2 ? 'En Proceso' : 'Inválido')))
        ];
    }
}
