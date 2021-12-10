<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandProduct extends Model
{
    public $timestamps = false;
    protected $table = 'marcas_productos';
    protected $primaryKey = 'producto_id';

    protected $guarded = [];

    protected $appends = [
        'id',
        'brand_id',
        'amount',
        'name',
        'includes',
        'expires_at',
        'providers',
        'created_at',
        'config',
        'variable',
        'active',
    ];

    protected $hidden = [
        'producto_id',          // BIGINT(20) NOT NULL AUTO_INCREMENT,
        'producto_marca',       // BIGINT(20) NOT NULL COMMENT 'ID de la Marca del Producto',
        'producto_monto',       // DECIMAL(20,2) NULL DEFAULT NULL,
        'producto_nombre',      // TEXT NOT NULL COLLATE 'utf8mb4_general_ci',
        'producto_incluye',     // TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
        'producto_vigencia',    // TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
        'producto_proveedores', // TEXT NOT NULL COMMENT 'json con producto_id de productos_proveedores, proveedor_id de la tabla proveedores' COLLATE 'utf8mb4_general_ci',
        'producto_creado',      // TIMESTAMP NOT NULL DEFAULT current_timestamp(),
        'producto_config',      // LONGTEXT NULL DEFAULT NULL COMMENT 'En Caso de Pago de Servicios y PINES' COLLATE 'utf8mb4_general_ci',
        'producto_libre',       // TINYINT(1) NULL DEFAULT '0' COMMENT '0 = Monto Fijo, 1 = Monto Libre',
        'producto_status',      // TINYINT(1) NOT NULL COMMENT '0 = Inactivo, 1 = Activo',
    ];

    protected $casts = [
        'producto_marca' => 'integer',
        'producto_monto' => 'float',
        'producto_proveedores' => 'json',
        'producto_creado' => 'datetime',
        'producto_libre' => 'boolean',
        'producto_status' => 'boolean',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'producto_marca');
    }

    public function getIdAttribute()
    {
        return $this->producto_id;
    }

    public function getBrandIdAttribute()
    {
        return $this->producto_marca;
    }

    public function getAmountAttribute()
    {
        return $this->producto_monto;
    }

    public function getNameAttribute()
    {
        return $this->producto_nombre;
    }

    public function getIncludesAttribute()
    {
        return $this->producto_incluye;
    }

    public function getExpiresAtAttribute()
    {
        return $this->producto_vigencia;
    }

    public function getProvidersAttribute()
    {
        return $this->producto_proveedores;
    }

    public function getCreatedAtAttribute()
    {
        return $this->producto_creado;
    }

    public function getConfigAttribute()
    {
        return $this->producto_config;
    }

    public function getVariableAttribute()
    {
        return $this->producto_libre;
    }

    public function getActiveAttribute()
    {
        return $this->producto_status;
    }
}
