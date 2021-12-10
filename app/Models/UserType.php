<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserType extends Model
{
    public $timestamps = false;
    protected $table = 'concesionarios_tipos';
    protected $primaryKey = 'tipo_id';

    protected $guarded = [];

    protected $appends = [
        'id',
        'name',
        'description',
        'parent_id',
        'created_at',
    ];

    protected $hidden = [
        'tipo_id',          // BIGINT(20) NOT NULL AUTO_INCREMENT,
        'tipo_nombre',      // VARCHAR(50) NULL DEFAULT NULL COMMENT 'Nombre del tipo de Usuario' COLLATE 'latin1_swedish_ci',
        'tipo_descripcion', // VARCHAR(255) NULL DEFAULT NULL COMMENT 'Descripcion del tipo de Usuario' COLLATE 'latin1_swedish_ci',
        'tipo_padre',       // BIGINT(20) NULL DEFAULT NULL COMMENT 'ID del Tipo de Usuario al que responde este tipo',
        'tipo_creado',      // TIMESTAMP NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha en que se creó el Tipo',
    ];

    protected $casts = [
        'tipo_padre' => 'integer',
        'tipo_creado' => 'datetime',
    ];

    public function parent()
    {
        return $this->belongsTo(UserType::class, 'tipo_padre');
    }

    public function getIdAttribute()
    {
        return $this->tipo_id;
    }

    public function getNameAttribute()
    {
        return $this->tipo_nombre;
    }

    public function getDescriptionAttribute()
    {
        return $this->tipo_descripcion;
    }

    public function getParentIdAttribute()
    {
        return $this->tipo_padre;
    }

    public function getCreatedAtAttribute()
    {
        return $this->tipo_creado;
    }
}
