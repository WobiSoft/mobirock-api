<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    public $timestamps = false;
    protected $table = 'seguridad_sesiones';
    protected $primaryKey = 'sesion_id';

    protected $guarded = [];

    protected $appends = [
        'id',
        'user_id',
        'device_uuid',
        'token',
        'connection_id',
        'started_at',
        'span',
        'ends_at',
        'logout_at',
        'code',
        'plataform',
        'latitude',
        'longitude',
        'active',
    ];

    protected $hidden = [
        'sesion_id',            // BIGINT(20) NOT NULL AUTO_INCREMENT,
        'sesion_concesionario', // BIGINT(20) NOT NULL COMMENT 'ID del Concesionario de esta Sesión',
        'sesion_dispositivo',   // TEXT NOT NULL COMMENT 'ID del Dispositivo desde el que se usa esta Sesión' COLLATE 'latin1_swedish_ci',
        'sesion_token',         // LONGTEXT NULL DEFAULT NULL COMMENT 'Token de la Sesión' COLLATE 'latin1_swedish_ci',
        'sesion_conexion',      // BIGINT(20) NULL DEFAULT NULL COMMENT 'ID de la Conexión IP en caso de ser por medio de la Plataforma WEB',
        'sesion_inicio',        // TIMESTAMP NULL DEFAULT NULL COMMENT 'Momento en que Inicia la Sesión',
        'sesion_duracion',      // BIGINT(20) NULL DEFAULT NULL COMMENT 'Segundos',
        'sesion_finaliza',      // DATETIME NULL DEFAULT NULL COMMENT 'Momento en que Finaliza la Sesión',
        'sesion_logout',        // DATETIME NULL DEFAULT NULL COMMENT 'Si el Concesionario cierra sesión antes del tiempo de Fin de la Sesión',
        'sesion_codigo',        // INT(4) NULL DEFAULT NULL,
        'sesion_plataforma',    // TINYINT(1) NULL DEFAULT '1' COMMENT '1 = Web App, 2 = Mobile App, 3 = Desktop App',
        'sesion_latitud',       // TEXT NULL DEFAULT NULL COMMENT 'Latitud desde donde se inició la Sesión' COLLATE 'latin1_swedish_ci',
        'sesion_longitud',      // TEXT NULL DEFAULT NULL COMMENT 'Longitud desde donde se inició la Sesión' COLLATE 'latin1_swedish_ci',
        'sesion_status',        // TINYINT(4) NULL DEFAULT NULL COMMENT '0 = Caducada, 1 = Activa',
    ];

    protected $casts = [
        'sesion_concesionario' => 'integer',
        'sesion_conexion' => 'integer',
        'sesion_inicio' => 'datetime',
        'sesion_duracion' => 'integer',
        'sesion_finaliza' => 'datetime',
        'sesion_logout' => 'datetime',
        'sesion_status' => 'boolean',
    ];

    public function getIdAttribute()
    {
        return $this->sesion_id;
    }

    public function getUserIdAttribute()
    {
        return $this->sesion_concesionario;
    }

    public function getDeviceUuidAttribute()
    {
        return $this->sesion_dispositivo;
    }

    public function getTokenAttribute()
    {
        return $this->sesion_token;
    }

    public function getConnectionIdAttribute()
    {
        return $this->sesion_conexion;
    }

    public function getStartedAtAttribute()
    {
        return $this->sesion_inicio;
    }

    public function getSpanAttribute()
    {
        return $this->sesion_duracion;
    }

    public function getEndsAtAttribute()
    {
        return $this->sesion_finaliza;
    }

    public function getLogoutAtAttribute()
    {
        return $this->sesion_logout;
    }

    public function getCodeAttribute()
    {
        return $this->sesion_codigo;
    }

    public function getPlataformAttribute()
    {
        return (object) [
            'id' => intval($this->sesion_plataforma),
            'name' => ($this->sesion_plataforma == 1 ? 'Web App' : ($this->sesion_plataforma == 2 ? 'Mobile App' : ($this->sesion_plataforma == 3 ? 'Desktop App' : 'Inválida')))
        ];
    }

    public function getLatitudeAttribute()
    {
        return $this->sesion_latitud;
    }

    public function getLongitudeAttribute()
    {
        return $this->sesion_longitud;
    }

    public function getActiveAttribute()
    {
        return $this->sesion_status;
    }
}
