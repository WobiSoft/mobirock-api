<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderCode extends Model
{
    public $timestamps = false;
    protected $table = 'proveedores_codigos';

    protected $guarded = [];

    protected $appends = [
        'provider_id',
        'code',
        'message',
    ];

    protected $hidden = [
        'proveedor', // BIGINT(20) NULL DEFAULT NULL,
        'codigo',    // VARCHAR(50) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'mensaje',   // LONGTEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
    ];

    protected $casts = [
        'proveedor' => 'integer',
    ];

    public function getProviderIdAttribute()
    {
        return $this->proveedor;
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'proveedor');
    }

    public function getCodeAttribute()
    {
        return $this->codigo;
    }

    public function getMessageAttribute()
    {
        return $this->mensaje;
    }
}
