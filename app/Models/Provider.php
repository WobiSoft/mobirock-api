<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    public $timestamps = false;
    protected $table = 'proveedores';
    protected $primaryKey = 'proveedor_id';

    protected $guarded = [];

    protected $appends = [
        'id',
        'name',
        'email',
        'tin',
        'contact',
        'phone',
        'address',
        'comment',
        'class',
        'balance',
        'current_balance',
        'negative',
        'fee_tae',
        'query',
        'created_at',
        'active',
    ];

    protected $hidden = [
        'proveedor_id',                      // BIGINT(20) NOT NULL AUTO_INCREMENT,
        'proveedor_nombre',                  // VARCHAR(150) NOT NULL COLLATE 'utf8mb4_general_ci',
        'proveedor_email',                   // VARCHAR(50) NOT NULL COLLATE 'utf8mb4_general_ci',
        'proveedor_rfc',                     // VARCHAR(13) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
        'proveedor_contacto',                // VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
        'proveedor_telefono',                // BIGINT(10) NOT NULL,
        'proveedor_direccion_calle',         // VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
        'proveedor_direccion_no_ext',        // VARCHAR(10) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
        'proveedor_direccion_no_int',        // VARCHAR(10) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
        'proveedor_direccion_entre_calle_1', // VARCHAR(20) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
        'proveedor_direccion_entre_calle_2', // VARCHAR(20) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
        'proveedor_direccion_colonia',       // VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
        'proveedor_direccion_estado',        // VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
        'proveedor_direccion_municipio',     // VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
        'proveedor_direccion_localidad',     // VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
        'proveedor_direccion_codigo_postal', // INT(5) NULL DEFAULT NULL,
        'proveedor_direccion_pais',          // TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
        'proveedor_comentario',              // TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
        'proveedor_clase',                   // TEXT NULL DEFAULT NULL COMMENT 'Clase a la que se llama' COLLATE 'utf8mb4_general_ci',
        'proveedor_ws_url',                  // TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
        'proveedor_ws_user',                 // TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
        'proveedor_ws_key',                  // TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
        'proveedor_ws_id',                   // TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
        'proveedor_ws_group',                // TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
        'proveedor_ws_timeout',              // TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
        'proveedor_saldo',                   // TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'Si muestra el Saldo en el WebService',
        'proveedor_saldo_actual',            // DECIMAL(12,2) NULL DEFAULT NULL COMMENT 'En el Caso de Proveedores que no muestran el Saldo en el WS',
        'proveedor_saldo_negativo',          // TINYINT(1) NOT NULL DEFAULT '0' COMMENT '0 = Saldo Positivo, 1 = Saldo Negativo',
        'proveedor_comision_tae',            // DECIMAL(12,2) NULL DEFAULT NULL COMMENT 'Comisión de REDPrepaid con el Proveedor',
        'proveedor_consulta',                // TINYINT(4) NOT NULL DEFAULT '1' COMMENT '0 = Si no se pueden Consultar Recargas, 1 = Si se pueden Consultar Recargas',
        'proveedor_creado',                  // TIMESTAMP NOT NULL DEFAULT current_timestamp(),
        'proveedor_status',                  // TINYINT(1) NOT NULL,
    ];

    protected $casts = [
        'proveedor_saldo' => 'boolean',
        'proveedor_saldo_actual' => 'float',
        'proveedor_saldo_negativo' => 'boolean',
        'proveedor_comision_tae' => 'float',
        'proveedor_consulta' => 'boolean',
        'proveedor_creado' => 'datetime',
        'proveedor_status' => 'boolean',
    ];

    public function getIdAttribute()
    {
        return $this->proveedor_id;
    }

    public function getNameAttribute()
    {
        return $this->proveedor_nombre;
    }

    public function getEmailAttribute()
    {
        return $this->proveedor_email;
    }

    public function getTinAttribute()
    {
        return $this->proveedor_rfc;
    }

    public function getContactAttribute()
    {
        return $this->proveedor_contacto;
    }

    public function getPhoneAttribute()
    {
        return $this->proveedor_telefono;
    }

    public function getAddressAttribute()
    {
        return (object) [
            'street'           => $this->proveedor_direccion_calle,
            'street_number'    => $this->proveedor_direccion_no_ext,
            'apartment_number' => $this->proveedor_direccion_no_int,
            'complement_1'     => $this->proveedor_direccion_entre_calle_1,
            'complement_2'     => $this->proveedor_direccion_entre_calle_2,
            'settlement'       => $this->proveedor_direccion_colonia,
            'state'            => $this->proveedor_direccion_estado,
            'municipality'     => $this->proveedor_direccion_municipio,
            'locality'         => $this->proveedor_direccion_localidad,
            'postal_code'      => $this->proveedor_direccion_codigo_postal,
            'country'          => $this->proveedor_direccion_pais,
        ];
    }

    public function getCommentAttribute()
    {
        return $this->proveedor_comentario;
    }

    public function getClassAttribute()
    {
        return $this->proveedor_clase;
    }

    public function getBalanceAttribute()
    {
        return $this->proveedor_saldo;
    }

    public function getCurrentBalanceAttribute()
    {
        return $this->proveedor_saldo_actual;
    }

    public function getNegativeAttribute()
    {
        return $this->proveedor_saldo_negativo;
    }

    public function getFeeTaeAttribute()
    {
        return $this->proveedor_comision_tae;
    }

    public function getQueryAttribute()
    {
        return $this->proveedor_consulta;
    }

    public function getCreatedAtAttribute()
    {
        return $this->proveedor_creado;
    }

    public function getActiveAttribute()
    {
        return $this->proveedor_status;
    }
}
