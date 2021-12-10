<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    public $timestamps = false;
    protected $table = 'concesionarios_dispositivos';
    protected $primaryKey = 'dispositivo_id';

    protected $guarded = [];

    protected $appends = [
        'id',
        'user_id',
        'name',
        'uuid',
        'plataform',
        'info',
        'last_session',
        'status',
    ];

    protected $hidden = [
        'dispositivo_id',            // BIGINT(20) NOT NULL AUTO_INCREMENT,
        'dispositivo_concesionario', // BIGINT(20) NOT NULL COMMENT 'ID del Concesionario al que Pertenece este Dispositivo',
        'dispositivo_nombre',        // TEXT NOT NULL COLLATE 'latin1_swedish_ci',
        'dispositivo_uuid',          // VARCHAR(255) NOT NULL COMMENT 'Identificador Único del Dispositivo' COLLATE 'latin1_swedish_ci',
        'dispositivo_plataforma',    // TEXT NOT NULL COMMENT '1 = WEB, 2 = Mobile, 3 = PC' COLLATE 'latin1_swedish_ci',
        'dispositivo_info',          // TEXT NOT NULL COMMENT 'JSON con la Información del Dispositivo' COLLATE 'latin1_swedish_ci',
        'dispositivo_ultima_sesion', // TIMESTAMP NULL DEFAULT current_timestamp() COMMENT 'Última ocasión en que inicio Sesión',
        'dispositivo_status',        // BIGINT(20) NOT NULL COMMENT '0 = Desactivado, 1 = Activado',
    ];

    protected $casts = [
        'dispositivo_concesionario' => 'integer',
        'dispositivo_info' => 'json',
        'dispositivo_ultima_sesion' => 'datetime',
        'dispositivo_status' => 'boolean',
    ];

    public function getIdAttribute()
    {
        return $this->dispositivo_id;
    }

    public function getUserIdAttribute()
    {
        return $this->dispositivo_concesionario;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'dispositivo_concesionario');
    }

    public function getNameAttribute()
    {
        return $this->dispositivo_nombre;
    }

    public function getUuidAttribute()
    {
        return $this->dispositivo_uuid;
    }

    public function push_token()
    {
        return $this->hasOne(UserPush::class, 'dispositivo_uuid', 'push_uuid');
    }

    public function getPlataformAttribute()
    {
        return (object) [
            'id' => intval($this->dispositivo_plataforma),
            'name' => ($this->dispositivo_plataforma == 1 ? 'Web App' : ($this->dispositivo_plataforma == 2 ? 'Mobile App' : ($this->dispositivo_plataforma == 3 ? 'Desktop App' : 'Inválida')))
        ];
    }

    public function getInfoAttribute()
    {
        return $this->dispositivo_info;
    }

    public function getLastSessionAttribute()
    {
        return $this->dispositivo_ultima_sesion;
    }

    public function getStatusAttribute()
    {
        return $this->dispositivo_status;
    }
}
