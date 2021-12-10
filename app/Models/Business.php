<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    public $timestamps = false;
    protected $table = 'concesionarios_negocios';
    protected $primaryKey = 'negocio_id';

    protected $guarded = [];

    protected $appends = [
        'id',
        'user_id',
        'type',
        'name',
        'phone',
        'line',
        'address',
        'created_at',
        'status',
    ];

    protected $hidden = [
        'negocio_id',                      // BIGINT(20) NOT NULL AUTO_INCREMENT,
        'negocio_concesionario',           // BIGINT(20) NULL DEFAULT NULL COMMENT 'ID del Concesionario',
        'negocio_tipo',                    // BIGINT(20) NULL DEFAULT NULL COMMENT '1 = Principal, 2 = Secundario',
        'negocio_nombre',                  // VARCHAR(255) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'negocio_telefono',                // VARCHAR(10) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'negocio_giro',                    // VARCHAR(255) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'negocio_domicilio_calle',         // VARCHAR(255) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'negocio_domicilio_no_ext',        // VARCHAR(10) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'negocio_domicilio_no_int',        // VARCHAR(10) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'negocio_domicilio_entre_calle_1', // VARCHAR(50) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'negocio_domicilio_entre_calle_2', // VARCHAR(50) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'negocio_domicilio_colonia',       // VARCHAR(50) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'negocio_domicilio_estado',        // VARCHAR(25) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'negocio_domicilio_municipio',     // VARCHAR(255) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'negocio_domicilio_localidad',     // VARCHAR(255) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'negocio_domicilio_cp',            // VARCHAR(5) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'negocio_domicilio_pais',          // VARCHAR(25) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'negocio_domicilio_latitud',       // VARCHAR(255) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'negocio_domicilio_longitud',      // VARCHAR(255) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'negocio_domicilio_creado',        // TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        'negocio_status',                  // TINYINT(4) NULL DEFAULT '1' COMMENT '0 = Desactivado, 1 = Activado',
    ];

    protected $casts = [
        'negocio_concesionario' => 'integer',
        'negocio_domicilio_creado' => 'datetime',
        'negocio_status' => 'boolean',
    ];

    public function getIdAttribute()
    {
        return $this->negocio_id;
    }

    public function getUserIdAttribute()
    {
        return $this->negocio_concesionario;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'negocio_concesionario');
    }

    public function getTypeAttribute()
    {
        return (object) [
            'id' => intval($this->negocio_tipo),
            'name' => ($this->negocio_tipo == 1 ? 'Principal' : ($this->negocio_tipo == 2 ? 'Secundario' : 'Inválido'))
        ];
    }

    public function getNameAttribute()
    {
        return $this->negocio_nombre;
    }

    public function getPhoneAttribute()
    {
        return $this->negocio_telefono;
    }

    public function getLineAttribute()
    {
        return $this->negocio_giro;
    }

    public function getAddressAttribute()
    {
        return (object) [
            'street'            => $this->negocio_domicilio_calle,
            'street_number'     => $this->negocio_domicilio_no_ext,
            'appartment_number' => $this->negocio_domicilio_no_int,
            'complement_1'      => $this->negocio_domicilio_entre_calle_1,
            'complement_2'      => $this->negocio_domicilio_entre_calle_2,
            'settlement'        => $this->negocio_domicilio_colonia,
            'state'             => $this->negocio_domicilio_estado,
            'municipality'      => $this->negocio_domicilio_municipio,
            'locality'          => $this->negocio_domicilio_localidad,
            'postal_code'       => $this->negocio_domicilio_cp,
            'country'           => $this->negocio_domicilio_pais,
            'latitude'          => $this->negocio_domicilio_latitud,
            'longitude'         => $this->negocio_domicilio_longitud,
        ];
    }

    public function getCreatedAtAttribute()
    {
        return $this->negocio_domicilio_creado;
    }

    public function getStatusAttribute()
    {
        return $this->negocio_status;
    }
}
