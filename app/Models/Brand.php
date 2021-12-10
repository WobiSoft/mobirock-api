<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    public $timestamps = false;
    protected $table = 'marcas';
    protected $primaryKey = 'marca_id';

    protected $guarded = [];

    protected $appends = [
        'id',
        'type',
        'name',
        'slug',
        'color',
        'description',
        'mobile_support',
        'phone_support',
        'created_at',
        'order',
        'receipt',
        'active',
    ];

    protected $hidden = [
        'marca_id',                // BIGINT(20) NOT NULL AUTO_INCREMENT,
        'marca_tipo',              // TINYINT(1) NOT NULL COMMENT '1 = TAE, 2 = PAQUETES DE DATOS, 3 = PDS, 4 = PINES ELECTRONICOS, 5 = TRANSPORTE',
        'marca_nombre',            // VARCHAR(50) NOT NULL COMMENT 'Nombre de la Marca' COLLATE 'utf8mb4_general_ci',
        'marca_slug',              // VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
        'marca_color',             // VARCHAR(255) NOT NULL DEFAULT 'rp' COLLATE 'utf8mb4_general_ci',
        'marca_descripcion',       // VARCHAR(50) NOT NULL COLLATE 'utf8mb4_general_ci',
        'marca_atencion_movil',    // TEXT NOT NULL COLLATE 'utf8mb4_general_ci',
        'marca_atencion_nacional', // TEXT NOT NULL COLLATE 'utf8mb4_general_ci',
        'marca_creada',            // TIMESTAMP NOT NULL DEFAULT current_timestamp(),
        'marca_orden',             // INT(11) NOT NULL,
        'marca_recibo',            // TINYINT(4) NULL DEFAULT '0' COMMENT 'Si el Pago de Servicios tiene un Recibo a la mano',
        'marca_status',            // TINYINT(1) NOT NULL DEFAULT '1' COMMENT '0 = Inactiva, 1 = Activa',
    ];

    protected $casts = [
        'marca_creada' => 'datetime',
        'marca_orden' => 'integer',
        'marca_recibo' => 'boolean',
        'marca_status' => 'boolean',
    ];

    public function getIdAttribute()
    {
        return $this->marca_id;
    }

    public function getTypeAttribute()
    {
        return (object) [
            'id' => intval($this->marca_tipo),
            'name' => ($this->marca_tipo == 1 ? 'Tiempo Aire Electrónico' : ($this->marca_tipo == 2 ? 'Paquetes de Datos' : ($this->marca_tipo == 3 ? 'Pago de Servicios' : ($this->marca_tipo == 4 ? 'Pines Electrónicos' : ($this->marca_tipo == 5 ? 'Transporte' : 'Inválido')))))
        ];
    }

    public function getNameAttribute()
    {
        return $this->marca_nombre;
    }

    public function getSlugAttribute()
    {
        return $this->marca_slug;
    }

    public function getColorAttribute()
    {
        return $this->marca_color;
    }

    public function getDescriptionAttribute()
    {
        return $this->marca_descripcion;
    }

    public function getMobileSupportAttribute()
    {
        return $this->marca_atencion_movil;
    }

    public function getPhoneSupportAttribute()
    {
        return $this->marca_atencion_nacional;
    }

    public function getCreatedAtAttribute()
    {
        return $this->marca_creada;
    }

    public function getOrderAttribute()
    {
        return $this->marca_orden;
    }

    public function getReceiptAttribute()
    {
        return $this->marca_recibo;
    }

    public function getActiveAttribute()
    {
        return $this->marca_status;
    }
}
