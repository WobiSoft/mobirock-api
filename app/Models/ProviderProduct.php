<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderProduct extends Model
{
    public $timestamps = false;
    protected $table = 'proveedores_productos';
    protected $primaryKey = 'producto_id';

    protected $guarded = [];

    protected $appends = [
        'id',
        'brand_id',
        'provider_id',
        'fee_provider',
        'fee_master',
        'fee_type',
        'code',
        'name',
        'amount',
        'includes',
        'expires_at',
        'fixed_amount',
        'min_amount',
        'max_amount',
        'created_at',
        'active',
    ];

    protected $hidden = [
        'producto_id',                 // BIGINT(20) NOT NULL AUTO_INCREMENT,
        'producto_marca',              // BIGINT(20) NOT NULL COMMENT 'ID de la Marca del Producto',
        'producto_proveedor',          // BIGINT(20) NOT NULL COMMENT 'ID del Proveedor del Producto',
        'producto_comision_proveedor', // DECIMAL(12,2) NULL DEFAULT '0.00' COMMENT 'Comisión cobrada por el Proveedor',
        'producto_comision_clickbox',  // DECIMAL(12,2) NULL DEFAULT '0.00' COMMENT 'Comisión cobrada por ClickBox',
        'producto_comision_tipo',      // TINYINT(1) UNSIGNED NULL DEFAULT '1' COMMENT '1 = Costo Fijo, 2 = Porcentaje,',
        'producto_codigo',             // VARCHAR(50) NOT NULL COMMENT 'Código del Producto' COLLATE 'utf8mb4_general_ci',
        'producto_nombre',             // VARCHAR(50) NOT NULL COMMENT 'Descripción del Producto' COLLATE 'utf8mb4_general_ci',
        'producto_importe',            // DECIMAL(20,2) NULL DEFAULT NULL COMMENT 'Importe del Producto',
        'producto_incluye',            // TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
        'producto_vigencia',           // VARCHAR(50) NULL DEFAULT NULL COMMENT 'Vigencia del Producto' COLLATE 'utf8mb4_general_ci',
        'producto_fijo',               // TINYINT(1) NULL DEFAULT '1' COMMENT '1 = Monto Fijo, 2 = Monto Variable,',
        'producto_minimo',             // DECIMAL(12,2) NULL DEFAULT NULL COMMENT 'Monto mínimo a pagar',
        'producto_maximo',             // DECIMAL(12,2) NULL DEFAULT NULL COMMENT 'Monto máximo a pagar',
        'producto_creado',             // TIMESTAMP NULL DEFAULT current_timestamp(),
        'producto_status',             // TINYINT(1) NOT NULL DEFAULT '1' COMMENT '0 = Desactivado, 1 = Activado',
    ];

    protected $casts = [
        'producto_marca' => 'integer',
        'producto_proveedor' => 'integer',
        'producto_comision_proveedor' => 'float',
        'producto_comision_clickbox' => 'float',
        'producto_importe' => 'float',
        'producto_fijo' => 'boolean',
        'producto_minimo' => 'float',
        'producto_maximo' => 'float',
        'producto_creado' => 'datetime',
        'producto_status' => 'boolean',
    ];

    public function getIdAttribute()
    {
        return $this->producto_id;
    }

    public function getBrandIdAttribute()
    {
        return $this->producto_marca;
    }

    public function getProviderIdAttribute()
    {
        return $this->producto_proveedor;
    }

    public function getFeeProviderAttribute()
    {
        return $this->producto_comision_proveedor;
    }

    public function getFeeMasterAttribute()
    {
        return $this->producto_comision_clickbox;
    }

    public function getFeeTypeAttribute()
    {
        return (object) [
            'id' => intval($this->producto_comision_tipo),
            'name' => ($this->producto_comision_tipo == 1 ? 'Costo Fijo' : ($this->producto_comision_tipo == 2 ? 'Porcentaje' : 'Inválido'))
        ];
    }

    public function getCodeAttribute()
    {
        return $this->producto_codigo;
    }

    public function getNameAttribute()
    {
        return $this->producto_nombre;
    }

    public function getAmountAttribute()
    {
        return $this->producto_importe;
    }

    public function getIncludesAttribute()
    {
        return $this->producto_incluye;
    }

    public function getExpiresAtAttribute()
    {
        return $this->producto_vigencia;
    }

    public function getFixedAmountAttribute()
    {
        return $this->producto_fijo;
    }

    public function getMinAmountAttribute()
    {
        return $this->producto_minimo;
    }

    public function getMaxAmountAttribute()
    {
        return $this->producto_maximo;
    }

    public function getCreatedAtAttribute()
    {
        return $this->producto_creado;
    }

    public function getActiveAttribute()
    {
        return $this->producto_status;
    }
}
