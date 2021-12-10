<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserOtp extends Model
{
    public $timestamps = false;
    protected $table = 'seguridad_codigos';
    protected $primaryKey = 'codigo_id';

    protected $guarded = [];

    protected $appends = [
        'id',
        'user_id',
        'code',
        'created_at',
        'used_at',
        'status',
    ];

    protected $hidden = [
        'codigo_id',            // BIGINT(20) NOT NULL AUTO_INCREMENT,
        'codigo_concesionario', // BIGINT(20) NOT NULL COMMENT 'ID del Concesionario al que pertence este Código',
        'codigo_digitos',       // VARCHAR(4) NOT NULL COMMENT 'Código de 4 Dígitos' COLLATE 'latin1_swedish_ci',
        'codigo_uuid',          // VARCHAR(255) NULL DEFAULT NULL COMMENT 'Idenficador Único de la Transacción' COLLATE 'latin1_swedish_ci',
        'codigo_creado',        // TIMESTAMP NULL DEFAULT current_timestamp() COMMENT 'Momento en el que fue Creado el Código',
        'codigo_vigencia',      // DATETIME NULL DEFAULT NULL COMMENT 'Momento en el que cadúca el Código',
        'codigo_utilizado',     // DATETIME NULL DEFAULT NULL COMMENT 'Momento en el que fue usado el Código',
        'codigo_status',        // TINYINT(1) NULL DEFAULT '0' COMMENT '0 = Creado, 1 = Utilizado',
    ];

    protected $casts = [
        'codigo_id' => 'integer',
        'codigo_concesionario' => 'integer',
        'codigo_creado' => 'datetime',
        'codigo_utilizado' => 'datetime',
        'codigo_status' => 'boolean',
    ];

    public function getIdAttribute()
    {
        return $this->codigo_id;
    }

    public function getUserIdAttribute()
    {
        return $this->codigo_concesionario;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'codigo_concesionario');
    }

    public function getCodeAttribute()
    {
        return $this->codigo_uuid;
    }

    public function getCreatedAtAttribute()
    {
        return $this->codigo_creado;
    }

    public function getUsedAtAttribute()
    {
        return $this->codigo_utilizado;
    }

    public function getStatusAttribute()
    {
        return $this->codigo_status;
    }
}
