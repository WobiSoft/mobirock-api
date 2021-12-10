<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPush extends Model
{
    public $timestamps = false;
    protected $table = 'concesionarios_push';
    protected $primaryKey = 'push_id';

    protected $guarded = [];

    protected $appends = [
        'id',
        'token',
        'uuid',
        'created_at',
        'status',
    ];

    protected $hidden = [
        'push_id',          // BIGINT(20) NOT NULL AUTO_INCREMENT,
        'push_suscripcion', // LONGTEXT NOT NULL COMMENT 'Datos de la Suscripcion' COLLATE 'latin1_swedish_ci',
        'push_uuid',        // VARCHAR(255) NOT NULL COMMENT 'UUID del Dispositivo' COLLATE 'latin1_swedish_ci',
        'push_arn',         // LONGTEXT NULL DEFAULT NULL COMMENT 'Amazon Resource Name para contactar con AWS' COLLATE 'latin1_swedish_ci',
        'push_creada',      // TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        'push_status',      // TINYINT(1) NOT NULL DEFAULT '1' COMMENT '0 = Inactiva, 1 = Activa',
    ];

    protected $casts = [
        'push_creada' => 'datetime',
        'push_status' => 'boolean',
    ];

    public function getIdAttribute()
    {
        return $this->push_id;
    }

    public function getTokenAttribute()
    {
        return $this->push_suscripcion;
    }

    public function getUuidAttribute()
    {
        return $this->push_uuid;
    }

    public function device()
    {
        return $this->hasOne(Device::class, 'push_uuid', 'dispositivo_uuid');
    }

    public function getCreatedAtAttribute()
    {
        return $this->push_creada;
    }

    public function getStatusAttribute()
    {
        return $this->push_status;
    }
}
