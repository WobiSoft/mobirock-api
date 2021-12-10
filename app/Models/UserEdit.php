<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserEdit extends Model
{
    public $timestamps = false;
    protected $table = 'concesionarios_ediciones';
    protected $primaryKey = 'edicion_id';

    protected $guarded = [];

    protected $appends = [
        'id',
        'user_id',
        'previous_value',
        'new_value',
        'requested_at',
        'processed_at',
        'code',
        'type',
        'status',
    ];

    protected $hidden = [
        'edicion_id',             // INT(11) NOT NULL AUTO_INCREMENT,
        'edicion_concesionario',  // INT(11) NOT NULL COMMENT 'ID del Concesionario al que pertenece la Edición',
        'edicion_valor_anterior', // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'edicion_valor_nuevo',    // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'edicion_solicitada',     // TIMESTAMP NOT NULL DEFAULT current_timestamp(),
        'edicion_realizada',      // DATETIME NULL DEFAULT NULL,
        'edicion_codigo',         // TEXT NOT NULL COMMENT 'En caso de Necesitar un Código de Autorización' COLLATE 'latin1_swedish_ci',
        'edicion_tipo',           // TINYINT(1) NOT NULL COMMENT '1 = Contraseña, 2 = Negocio, 3 = Email, 4 = Movil, 5 = Telefono',
        'edicion_status',         // TINYINT(1) NOT NULL DEFAULT '0' COMMENT '0 = En Proceso, 1 = Finalizada',
    ];

    protected $casts = [
        'edicion_concesionario' => 'integer',
        'edicion_valor_anterior' => 'json',
        'edicion_valor_nuevo' => 'json',
        'edicion_status' => 'boolean',
        'edicion_solicitada' => 'datetime',
        'edicion_realizada' => 'datetime',
    ];

    public function getIdAttribute()
    {
        return $this->edicion_id;
    }

    public function getUserIdAttribute()
    {
        return $this->edicion_concesionario;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'edicion_concesionario');
    }

    public function getPreviousValueAttribute()
    {
        return $this->edicion_valor_anterior;
    }

    public function getNewValueAttribute()
    {
        return $this->edicion_valor_nuevo;
    }

    public function getRequestedAtAttribute()
    {
        return $this->edicion_solicitada;
    }

    public function getProcessedAtAttribute()
    {
        return $this->edicion_realizada;
    }

    public function getCodeAttribute()
    {
        return $this->edicion_codigo;
    }

    public function getTypeAttribute()
    {
        return (object) [
            'id' => intval($this->edicion_tipo),
            'name' => ($this->edicion_tipo == 1 ? 'Contraseña' : ($this->edicion_tipo == 2 ? 'Negocio' : ($this->edicion_tipo == 3 ? 'Email' : ($this->edicion_tipo == 4 ? 'Movil' : ($this->edicion_tipo == 5 ? 'Telefono' : 'Inválido')))))
        ];
    }

    public function getStatusAttribute()
    {
        return $this->edicion_status;
    }
}
