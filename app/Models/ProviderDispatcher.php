<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderDispatcher extends Model
{
    public $timestamps = false;
    protected $table = 'proveedores_dispatcher';
    protected $primaryKey = 'dispatcher_id';

    protected $guarded = [];

    protected $appends = [
        'id',
        'brand_id',
        'provider_id',
        'providers',
    ];

    protected $hidden = [
        'dispatcher_id',          // BIGINT(20) NOT NULL AUTO_INCREMENT,
        'dispatcher_marca',       // BIGINT(20) NOT NULL COMMENT 'ID de la Marca del Dispatcher',
        'dispatcher_proveedor',   // BIGINT(20) NOT NULL COMMENT 'ID del Proveedor',
        'dispatcher_proveedores', // VARCHAR(50) NOT NULL COLLATE 'utf8mb4_general_ci',
    ];

    protected $casts = [
        'dispatcher_marca' => 'integer',
        'dispatcher_proveedor' => 'integer',
        'dispatcher_proveedores' => 'json',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'dispatcher_marca');
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'dispatcher_proveedor');
    }

    public function getIdAttribute()
    {
        return $this->dispatcher_id;
    }

    public function getBrandIdAttribute()
    {
        return $this->dispatcher_marca;
    }

    public function getProviderIdAttribute()
    {
        return $this->dispatcher_proveedor;
    }

    public function getProvidersAttribute()
    {
        return $this->dispatcher_proveedores;
    }
}
