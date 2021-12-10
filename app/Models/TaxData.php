<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxData extends Model
{
    public $timestamps = false;
    protected $table = 'concesionarios_datos_fiscales';
    protected $primaryKey = 'datos_id';

    protected $guarded = [];

    protected $appends = [
        'type',
        'issuer',
        'id',
        'user_id',
        'business_name',
        'tin',
        'curp',
        'email',
        'phone',
        'regime',
        'address',
        'use',
        'id_card',
        'logo',
        'certificate',
        'certificate_key',
        'certificate_password',
        'color',
        'series',
        'created_at',
        'csd',
        'status',
    ];

    protected $hidden = [
        'datos_id',                  // BIGINT(20) NOT NULL AUTO_INCREMENT,
        'datos_concesionario',       // BIGINT(20) NOT NULL COMMENT '	ID del Concesionario al que pertenecen los Datos Fiscales',
        'datos_razon_social',        // VARCHAR(250) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'datos_rfc',                 // VARCHAR(13) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'datos_curp',                // VARCHAR(20) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'datos_email',               // VARCHAR(255) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'datos_telefono',            // VARCHAR(10) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'datos_tipo',                // TINYINT(1) NULL DEFAULT NULL COMMENT '1 = Persona Física, 2 = Persona Moral',
        'datos_regimen',             // VARCHAR(5) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'datos_domicilio_calle',     // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'datos_domicilio_noext',     // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'datos_domicilio_noint',     // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'datos_domicilio_colonia',   // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'datos_domicilio_localidad', // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'datos_domicilio_municipio', // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'datos_domicilio_estado',    // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'datos_domicilio_pais',      // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'datos_domicilio_cp',        // VARCHAR(6) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'datos_uso',                 // VARCHAR(6) NULL DEFAULT 'G03' COMMENT 'Uso que se le va a dar al CFDI' COLLATE 'latin1_swedish_ci',
        'datos_cedula',              // LONGTEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'datos_logotipo',            // LONGTEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'datos_certificado',         // LONGTEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'datos_llave',               // LONGTEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'datos_clave',               // LONGTEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'datos_color',               // VARCHAR(6) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'datos_serie',               // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'datos_creados',             // TIMESTAMP NOT NULL DEFAULT current_timestamp(),
        'datos_emisor',              // TINYINT(1) NOT NULL DEFAULT '0' COMMENT '0 = No es Emisor, 1 = Si es Emisor, 2 = Solicitó ser Emisor, 3 = Falta conrfimación del Servicio',
        'datos_csd',                 // TINYINT(1) NULL DEFAULT NULL COMMENT '0 = No válido, 1 = Válido',
        'datos_status',              // TINYINT(1) NOT NULL DEFAULT '0' COMMENT '0 = No Puede Facturar, 1 = Puede Facturar',
    ];

    protected $casts = [
        'datos_concesionario' => 'integer',
        'datos_csd' => 'boolean',
        'datos_status' => 'boolean',
        'datos_creados' => 'datetime',
    ];

    public function getTypeAttribute()
    {
        return (object) [
            'id' => $this->datos_tipo,
            'name' => ($this->datos_tipo == 1 ? 'Persona Física' : ($this->datos_tipo == 2 ? 'Persona Moral' : 'Inválido')),
        ];
    }

    public function getIssuerAttribute()
    {
        return (object) [
            'id' => $this->datos_tipo,
            'name' => ($this->datos_emisor == 1 ? 'Emisor' : ($this->datos_emisor == 2 ? 'Solicitó ser Emisor' : ($this->datos_emisor == 3 ? 'Falta conrfimación del Servicio' : 'Inválido'))),
        ];
    }

    public function getAddressAttribute()
    {
        return (object) [
            'street'           => $this->datos_domicilio_calle,
            'street_number'    => $this->datos_domicilio_noext,
            'apartment_number' => $this->datos_domicilio_noint,
            'settlement'       => $this->datos_domicilio_colonia,
            'locality'         => $this->datos_domicilio_localidad,
            'municipality'     => $this->datos_domicilio_municipio,
            'state'            => $this->datos_domicilio_estado,
            'country'          => $this->datos_domicilio_pais,
            'postal_code'      => $this->datos_domicilio_cp,
        ];
    }

    public function getIdAttribute()
    {
        return $this->datos_id;
    }

    public function getUserIdAttribute()
    {
        return $this->datos_concesionario;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'datos_concesionario');
    }

    public function getBusinessNameAttribute()
    {
        return $this->datos_razon_social;
    }

    public function getTinAttribute()
    {
        return $this->datos_rfc;
    }

    public function getCurpAttribute()
    {
        return $this->datos_curp;
    }

    public function getEmailAttribute()
    {
        return $this->datos_email;
    }

    public function getPhoneAttribute()
    {
        return $this->datos_telefono;
    }

    public function getRegimeAttribute()
    {
        return $this->datos_regimen;
    }

    public function getUseAttribute()
    {
        return $this->datos_uso;
    }

    public function getIdCardAttribute()
    {
        return $this->datos_cedula;
    }

    public function getLogoAttribute()
    {
        return $this->datos_logotipo;
    }

    public function getCertificateAttribute()
    {
        return $this->datos_certificado;
    }

    public function getCertificateKeyAttribute()
    {
        return $this->datos_llave;
    }

    public function getCertificatePasswordAttribute()
    {
        return $this->datos_clave;
    }

    public function getColorAttribute()
    {
        return $this->datos_color;
    }

    public function getSeriesAttribute()
    {
        return $this->datos_serie;
    }

    public function getCreatedAtAttribute()
    {
        return $this->datos_creados;
    }

    public function getCsdAttribute()
    {
        return $this->datos_csd;
    }

    public function getStatusAttribute()
    {
        return $this->datos_status;
    }
}
