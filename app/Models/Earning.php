<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Earning extends Model
{
    public $timestamps = false;
    protected $table = 'concesionarios_utilidad';

    protected $guarded = [];

    protected $appends = [
        'parent_id',
        'child_id',
        'payment_id',
        'parent_fee',
        'child_fee',
        'difference_fee',
        'amount',
        'profit',
        'created_at',
        'validated_at',
    ];

    protected $hidden = [
        'padre',          // BIGINT(20) NULL DEFAULT NULL,
        'hijo',           // BIGINT(20) NULL DEFAULT NULL,
        'pago',           // BIGINT(20) NULL DEFAULT NULL,
        'comision_padre', // DECIMAL(12,2) NULL DEFAULT NULL,
        'comision_hijo',  // DECIMAL(12,2) NULL DEFAULT NULL,
        'diferencia',     // DECIMAL(12,2) NULL DEFAULT NULL,
        'monto',          // DECIMAL(12,2) NULL DEFAULT NULL,
        'utilidad',       // DECIMAL(12,2) NULL DEFAULT NULL,
        'fecha',          // DATETIME NULL DEFAULT NULL,
        'validado',       // TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        'status',         // TINYINT(1) NULL DEFAULT '1' COMMENT '1 = Generada',
    ];

    protected $casts = [
        'padre' => 'integer',
        'hijo' => 'integer',
        'pago' => 'integer',
        'comision_padre' => 'float',
        'comision_hijo' => 'float',
        'diferencia' => 'float',
        'monto' => 'float',
        'utilidad' => 'float',
        'fecha' => 'datetime',
        'validado' => 'datetime',
        'status' => 'boolean',
    ];

    public function getParentIdAttribute()
    {
        return $this->padre;
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'padre');
    }

    public function getChildIdAttribute()
    {
        return $this->hijo;
    }

    public function child()
    {
        return $this->belongsTo(User::class, 'hijo');
    }

    public function getPaymentIdAttribute()
    {
        return $this->pago;
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'pago');
    }

    public function getParentFeeAttribute()
    {
        return $this->comision_padre;
    }

    public function getChildFeeAttribute()
    {
        return $this->comision_hijo;
    }

    public function getDifferenceFeeAttribute()
    {
        return $this->diferencia;
    }

    public function getAmountAttribute()
    {
        return $this->monto;
    }

    public function getProfitAttribute()
    {
        return $this->utilidad;
    }

    public function getCreatedAtAttribute()
    {
        return $this->fecha;
    }

    public function getValidatedAtAttribute()
    {
        return $this->validado;
    }
}
