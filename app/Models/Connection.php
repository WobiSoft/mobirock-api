<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Connection extends Model
{
    public $timestamps = false;
    protected $table = 'seguridad_conexiones';
    protected $primaryKey = 'conexion_id';

    protected $guarded = [];

    protected $appends = [
        'id',
        'user_id',
        'name',
        'created_at',
        'expired_at',
        'blocked_at',
        'ip',
        'latitude',
        'longitude',
        'comments',
        'bypass',
        'status',
    ];

    protected $hidden = [
        'conexion_id',            // BIGINT(20) NOT NULL AUTO_INCREMENT,
        'conexion_concesionario', // BIGINT(20) NULL DEFAULT NULL COMMENT 'ID del Concesionario que autorizó',
        'conexion_nombre',        // VARCHAR(255) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'conexion_creada',        // TIMESTAMP NOT NULL DEFAULT current_timestamp() COMMENT 'Momento en que se creó la Conexión',
        'conexion_caducada',      // DATETIME NULL DEFAULT NULL COMMENT 'Momento en que caducó la Conexión',
        'conexion_bloqueada',     // DATETIME NULL DEFAULT NULL COMMENT 'Momento en que se bloqueó la Conexión',
        'conexion_ip',            // VARCHAR(255) NOT NULL COMMENT 'IP de la Conexión' COLLATE 'latin1_swedish_ci',
        'conexion_latitud',       // FLOAT NULL DEFAULT NULL,
        'conexion_longitud',      // FLOAT NULL DEFAULT NULL,
        'conexion_observaciones', // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'conexion_bypass',        // TINYINT(4) NOT NULL DEFAULT '0' COMMENT '0 = Cuenta con Seguridad IP, 1 = No cuenta con Seguridad IP',
        'conexion_status',        // TINYINT(1) NOT NULL DEFAULT '2' COMMENT '0 = Bloqueada, 1 = Autorizada, 2 = Por Autorizar, 9 = Eliminada',
    ];

    protected $casts = [
        'conexion_concesionario' => 'integer',
        'conexion_creada' => 'datetime',
        'conexion_caducada' => 'datetime',
        'conexion_bloqueada' => 'datetime',
        'conexion_bypass' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'conexion_concesionario');
    }

    public function getIdAttribute()
    {
        return $this->conexion_id;
    }

    public function getUserIdAttribute()
    {
        return $this->conexion_concesionario;
    }

    public function getNameAttribute()
    {
        return $this->conexion_nombre;
    }

    public function getCreatedAtAttribute()
    {
        return $this->conexion_creada;
    }

    public function getExpiredAtAttribute()
    {
        return $this->conexion_caducada;
    }

    public function getBlockedAtAttribute()
    {
        return $this->conexion_bloqueada;
    }

    public function getIpAttribute()
    {
        return $this->conexion_ip;
    }

    public function getLatitudeAttribute()
    {
        return $this->conexion_latitud;
    }

    public function getLongitudeAttribute()
    {
        return $this->conexion_longitud;
    }

    public function getCommentsAttribute()
    {
        return $this->conexion_observaciones;
    }

    public function getBypassAttribute()
    {
        return $this->conexion_bypass;
    }

    public function getStatusAttribute()
    {
        return (object) [
            'id' => intval($this->conexion_status),
            'name' => ($this->conexion_status == 0 ? 'Bloqueada' : ($this->conexion_status == 1 ? 'Autorizada' : ($this->conexion_status == 2 ? 'Por Autorizar' : ($this->conexion_status == 9 ? 'Eliminada' : 'Inválido'))))
        ];
    }
}
