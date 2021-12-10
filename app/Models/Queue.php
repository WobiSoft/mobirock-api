<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    public $timestamps = false;
    protected $table = 'cola';

    protected $guarded = [];

    protected $appends = [
        'user_id',
        'product',
        'created_at',
    ];

    protected $hidden = [
        'id',       // BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        'cliente',  // BIGINT(20) UNSIGNED NULL DEFAULT NULL,
        'producto', // BIGINT(20) UNSIGNED NULL DEFAULT NULL COMMENT '1 = TAE y Paquetes, 2 = Pago de Servicios',
        'creada',   // TIMESTAMP NULL DEFAULT current_timestamp(),
    ];

    protected $casts = [
        'cliente' => 'integer',
        'creada' => 'datetime',
    ];

    public function getUserIdAttribute()
    {
        return $this->cliente;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'cliente');
    }

    public function getProductAttribute()
    {
        return (object) [
            'id' => intval($this->producto),
            'name' => ($this->producto == 1 ? 'Tiempo Aire Electrónico' : ($this->producto == 2 ? 'Pago de Servicios' : 'Inválido'))
        ];
    }

    public function getCreatedAtAttribute()
    {
        return $this->creada;
    }
}
