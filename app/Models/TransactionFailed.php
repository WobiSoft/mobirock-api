<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionFailed extends Model
{
    public $timestamps = false;
    protected $table = 'transacciones_fallidas';

    protected $guarded = [];

    protected $appends = [
        'user_id',
        'user_name',
        'provider_id',
        'provider_name',
        'code',
        'message',
        'started_at',
        'ended_at',
        'description',
        'brand_id',
        'brand_name',
        'reference',
        'amount',
        'uuid',
        'identifier',
    ];

    protected $hidden = [
        'cliente',          // BIGINT(20) NULL DEFAULT NULL,
        'cliente_nombre',   // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'proveedor',        // BIGINT(20) NULL DEFAULT NULL,
        'proveedor_nombre', // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'codigo',           // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'error',            // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'iniciada',         // DATETIME NULL DEFAULT NULL,
        'finalizada',       // DATETIME NULL DEFAULT NULL,
        'descripcion',      // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'request',          // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'response',         // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'marca',            // BIGINT(20) NULL DEFAULT NULL,
        'marca_nombre',     // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'referencia',       // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'monto',            // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'uuid',             // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'cdr',              // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
    ];

    protected $casts = [
        'cliente' => 'integer',
        'proveedor' => 'integer',
        'marca' => 'integer',
        'iniciada' => 'datetime',
        'finalizada' => 'datetime',
        'request' => 'json',
        'response' => 'json',
        'monto' => 'float',
    ];

    public function getUserIdAttribute()
    {
        return $this->cliente;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'cliente');
    }

    public function getUserNameAttribute()
    {
        return $this->cliente_nombre;
    }

    public function getProviderIdAttribute()
    {
        return $this->proveedor;
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'proveedor');
    }

    public function getProviderNameAttribute()
    {
        return $this->proveedor_nombre;
    }

    public function getCodeAttribute()
    {
        return $this->codigo;
    }

    public function getMessageAttribute()
    {
        return $this->error;
    }

    public function getStartedAtAttribute()
    {
        return $this->iniciada;
    }

    public function getEndedAtAttribute()
    {
        return $this->finalizada;
    }

    public function getDescriptionAttribute()
    {
        return $this->descripcion;
    }

    public function getBrandIdAttribute()
    {
        return $this->marca;
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'marca');
    }

    public function getBrandNameAttribute()
    {
        return $this->marca_nombre;
    }

    public function getReferenceAttribute()
    {
        return $this->referencia;
    }

    public function getAmountAttribute()
    {
        return $this->monto;
    }

    public function getUuidAttribute()
    {
        return $this->uuid;
    }

    public function getIdentifierAttribute()
    {
        return $this->cdr;
    }
}
