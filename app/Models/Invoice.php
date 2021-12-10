<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    public $timestamps = false;
    protected $table = 'concesionarios_facturas';
    protected $primaryKey = 'factura_id';

    protected $guarded = [];

    protected $appends = [
        'id',
        'user_id',
        'tax_data_id',
        'payment_id',
        'registered_at',
        'uuid',
        'pdf',
        'xml',
        'status',
    ];

    protected $hidden = [
        'factura_id',            // BIGINT(20) NOT NULL AUTO_INCREMENT,
        'factura_concesionario', // BIGINT(20) NOT NULL COMMENT 'ID del Concesionario al que pertenece la Factura',
        'factura_datos',         // BIGINT(20) NULL DEFAULT NULL COMMENT 'ID de los Datos Fiscales de la Factura',
        'factura_pago',          // BIGINT(20) NULL DEFAULT NULL COMMENT 'ID del al que va ligada la Factura',
        'factura_registrada',    // TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        'factura_uuid',          // VARCHAR(36) NOT NULL COLLATE 'latin1_swedish_ci',
        'factura_pdf',           // VARCHAR(255) NOT NULL COMMENT 'URL del archivo PDF' COLLATE 'latin1_swedish_ci',
        'factura_xml',           // VARCHAR(255) NOT NULL COMMENT 'URL del archivo XML' COLLATE 'latin1_swedish_ci',
        'factura_status',        // TINYINT(1) NOT NULL DEFAULT '1' COMMENT '0 = Cancelada, 1 = Generada',
    ];

    protected $casts = [
        'factura_concesionario' => 'integer',
        'factura_datos' => 'integer',
        'factura_pago' => 'integer',
        'factura_registrada' => 'datetime',
        'factura_status' => 'boolean',
    ];

    public function getIdAttribute()
    {
        return $this->factura_id;
    }

    public function getUserIdAttribute()
    {
        return $this->factura_concesionario;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'factura_concesionario');
    }

    public function getTaxDataIdAttribute()
    {
        return $this->factura_datos;
    }

    public function tax_data()
    {
        return $this->belongsTo(TaxData::class, 'factura_datos');
    }

    public function getPaymentIdAttribute()
    {
        return $this->factura_pago;
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'factura_pago');
    }

    public function getRegisteredAtAttribute()
    {
        return $this->factura_registrada;
    }

    public function getUuidAttribute()
    {
        return $this->factura_uuid;
    }

    public function getPdfAttribute()
    {
        return $this->factura_pdf;
    }

    public function getXmlAttribute()
    {
        return $this->factura_xml;
    }

    public function getStatusAttribute()
    {
        return $this->factura_status;
    }
}
