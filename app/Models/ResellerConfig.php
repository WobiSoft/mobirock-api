<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResellerConfig extends Model
{
    public $timestamps = false;
    protected $table = 'config_resellers';

    protected $guarded = [];

    protected $appends = [
        'user_id',
        'platform',
        'name',
        'share_accounts',
        'accounts_ids',
        'receipt',
    ];

    protected $hidden = [
        'concesionario',         // BIGINT(20) UNSIGNED NULL DEFAULT NULL,
        'plataforma',            // TINYINT(1) UNSIGNED NULL DEFAULT '1' COMMENT '1 = Provicel, 2 = Hostlander',
        'nombre',                // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'compartir_cuentas',     // TINYINT(4) UNSIGNED NULL DEFAULT '0' COMMENT '0 = No se comparten, 1 = Si se comparten',
        'compartir_cuentas_pds', // TINYINT(4) UNSIGNED NULL DEFAULT '1' COMMENT '0 = No se comparten, 1 = Si se comparten',
        'cuentas',               // TEXT NULL DEFAULT NULL COMMENT 'Las cuentas que se comparten con su red' COLLATE 'latin1_swedish_ci',
        'comprobante',           // TINYINT(1) NULL DEFAULT '1' COMMENT '1 = Puede elegir, 2 = No puede elegir',
        'status',                // TINYINT(1) UNSIGNED NULL DEFAULT '1' COMMENT '0 = Eliminado, 1 = Activo',
    ];

    protected $casts = [
        'concesionario' => 'integer',
        'compartir_cuentas' => 'boolean',
        'compartir_cuentas_pds' => 'boolean',
        'cuentas' => 'json',
        'comprobante' => 'integer',
        'status' => 'boolean',
    ];

    public function getUserIdAttribute()
    {
        return $this->concesionario;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'concesionario');
    }

    public function getPlatformAttribute()
    {
        return (object) [
            'id' => intval($this->plataforma),
            'name' => ($this->plataforma == 1 ? 'Provicel' : ($this->plataforma == 2 ? 'Hostlander' : 'Inválida'))
        ];
    }

    public function getNameAttribute()
    {
        return $this->nombre;
    }

    public function getShareAccountsAttribute()
    {
        return (object) [
            'prepaid' => $this->compartir_cuentas,
            'services' => $this->compartir_cuentas_pds,
        ];
    }

    public function getAccountsIdsAttribute()
    {
        return $this->cuentas;
    }

    public function getAccountsAttribute()
    {
        return Account::whereIn('cuenta_id', $this->cuentas)->get();
    }

    public function getReceiptAttribute()
    {
        return (object) [
            'id' => intval($this->comprobante),
            'name' => ($this->comprobante == 1 ? 'Puede elegir' : ($this->comprobante == 2 ? 'No puede elegir' : 'Inválido'))
        ];
    }
}
