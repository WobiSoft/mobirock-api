<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    public $timestamps = false;
    protected $table = 'cuentas';
    protected $primaryKey = 'cuenta_id';

    protected $guarded = [];

    protected $appends = [
        'id',
        'user_id',
        'method_id',
        'bank_id',
        'digits',
        'name',
        'branch',
        'number',
        'clabe',
        'extra',
        'level',
        'cover',
        'created_at',
        'activated_at',
        'deleted_at',
        'deleted_reason',
        'status',
    ];

    protected $hidden = [
        'cuenta_id',            // BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        'cuenta_concesionario', // BIGINT(20) NOT NULL COMMENT 'ID del Concesionario al que pertenece la cuenta',
        'cuenta_forma',         // BIGINT(20) NOT NULL COMMENT 'ID de la Forma de Pago de la Cuenta',
        'cuenta_banco',         // BIGINT(20) NULL DEFAULT NULL COMMENT 'ID del Banco al que Pertence la Cuenta',
        'cuenta_digitos',       // VARCHAR(4) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'cuenta_nombre',        // VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
        'cuenta_sucursal',      // VARCHAR(255) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'cuenta_numero',        // VARCHAR(255) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'cuenta_clabe',         // VARCHAR(255) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'cuenta_extra',         // VARCHAR(255) NULL DEFAULT NULL COMMENT 'Referencia ej. email, telefono, cuenta.' COLLATE 'latin1_swedish_ci',
        'cuenta_nivel',         // TINYINT(1) NOT NULL DEFAULT '1' COMMENT '1 = Toda la RED, 2 = Solo RED del concesionario',
        'cuenta_caratula',      // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'cuenta_creada',        // TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y Hora en que se creó la cuenta',
        'cuenta_activada',      // DATETIME NULL DEFAULT NULL COMMENT 'Fecha y Hora en que la activo un Master',
        'cuenta_eliminada',     // DATETIME NULL DEFAULT NULL,
        'cuenta_razon',         // TEXT NULL DEFAULT NULL COMMENT 'Razón de la Eliminación por parte de un Master' COLLATE 'latin1_swedish_ci',
        'cuenta_status',        // TINYINT(1) NOT NULL DEFAULT '2' COMMENT '0 = Eliminada, 1 = Autorizada, 2 = Por Autorizar',
    ];

    protected $casts = [
        'cuenta_concesionario' => 'integer',
        'cuenta_forma' => 'integer',
        'cuenta_banco' => 'integer',
        'cuenta_creada' => 'datetime',
        'cuenta_activada' => 'datetime',
        'cuenta_eliminada' => 'datetime',
    ];

    public function getIdAttribute()
    {
        return $this->cuenta_id;
    }

    public function getUserIdAttribute()
    {
        return $this->cuenta_concesionario;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'cuenta_concesionario');
    }

    public function getMethodIdAttribute()
    {
        return $this->cuenta_forma;
    }

    public function method()
    {
        return $this->belongsTo(Method::class, 'cuenta_forma');
    }

    public function getBankIdAttribute()
    {
        return $this->cuenta_banco;
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'cuenta_banco');
    }

    public function getDigitsAttribute()
    {
        return $this->cuenta_digitos;
    }

    public function getNameAttribute()
    {
        return $this->cuenta_nombre;
    }

    public function getBranchAttribute()
    {
        return $this->cuenta_sucursal;
    }

    public function getNumberAttribute()
    {
        return $this->cuenta_numero;
    }

    public function getClabeAttribute()
    {
        return $this->cuenta_clabe;
    }

    public function getExtraAttribute()
    {
        return $this->cuenta_extra;
    }

    public function getLevelAttribute()
    {
        return (object) [
            'id' => intval($this->cuenta_nivel),
            'name' => ($this->cuenta_nivel == 1 ? 'Toda la RED' : ($this->cuenta_nivel == 2 ? 'Solo RED del concesionario' : 'Inválido'))
        ];
    }

    public function getCoverAttribute()
    {
        return $this->cuenta_caratula;
    }

    public function getCreatedAtAttribute()
    {
        return $this->cuenta_creada;
    }

    public function getActivatedAtAttribute()
    {
        return $this->cuenta_activada;
    }

    public function getDeletedAtAttribute()
    {
        return $this->cuenta_eliminada;
    }

    public function getDeletedReasonAttribute()
    {
        return $this->cuenta_razon;
    }

    public function getStatusAttribute()
    {
        return $this->cuenta_status;

        return (object) [
            'id' => intval($this->cuenta_status),
            'name' => ($this->cuenta_status == 0 ? 'Eliminada' : ($this->cuenta_status == 1 ? 'Autorizada' : ($this->cuenta_status == 2 ? 'Por Autorizar' : 'Inválido')))
        ];
    }
}
