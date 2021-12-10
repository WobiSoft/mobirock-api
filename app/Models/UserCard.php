<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCard extends Model
{
    public $timestamps = false;
    protected $table = 'concesionarios_tarjetas';
    protected $primaryKey = 'tarjeta_id';

    protected $guarded = [];

    protected $appends = [
        'id',
        'user_id',
        'customer',
        'token',
        'identifier',
        'owner',
        'digits',
        'brand',
        'expires_at',
        'bank',
        'default',
        'provider_id',
        'max_amount',
        'created_at',
        'salt',
        'doc_id_front',
        'doc_id_back',
        'doc_permission',
        'doc_card_front',
        'rejected_reason',
        'status',
    ];

    protected $hidden = [
        'tarjeta_id',             // BIGINT(20) NOT NULL AUTO_INCREMENT,
        'tarjeta_concesionario',  // BIGINT(20) NOT NULL COMMENT 'ID del Concesionario al que pertenece la tarjeta',
        'tarjeta_customer',       // TEXT NULL DEFAULT NULL COMMENT 'ID del Cliente en la Plataforma de pago (ej. customer id en Conekta)' COLLATE 'latin1_swedish_ci',
        'tarjeta_token',          // TEXT NULL DEFAULT NULL COMMENT 'Token usado para identificar la tarjeta con el servicio' COLLATE 'latin1_swedish_ci',
        'tarjeta_identificador',  // TEXT NULL DEFAULT NULL COMMENT 'Identificador de la Tarjeta con el Servicio' COLLATE 'latin1_swedish_ci',
        'tarjeta_nombre',         // VARCHAR(255) NOT NULL COMMENT 'Nombre que aparece en la Tarjeta' COLLATE 'latin1_swedish_ci',
        'tarjeta_digitos',        // VARCHAR(4) NOT NULL COMMENT 'Últimos 4 dígitos de la tarjeta' COLLATE 'latin1_swedish_ci',
        'tarjeta_marca',          // VARCHAR(255) NOT NULL COMMENT 'Visa, Mastercard, etc.' COLLATE 'latin1_swedish_ci',
        'tarjeta_vigencia',       // VARCHAR(7) NOT NULL COMMENT 'Vigencia de la Tarjeta' COLLATE 'latin1_swedish_ci',
        'tarjeta_banco',          // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'tarjeta_default',        // TINYINT(1) NOT NULL DEFAULT '0' COMMENT '0 = No es la Predeterminada, 1 = Es la Predeterminada',
        'tarjeta_proveedor',      // BIGINT(20) NULL DEFAULT '1' COMMENT 'ID del Proveedor que proporciona la interfaz de pago con Tarjeta',
        'tarjeta_maximo',         // DECIMAL(12,2) NULL DEFAULT NULL COMMENT 'Monto Máximo que se le Cobrará a esa Tarjeta',
        'tarjeta_creada',         // TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha en que se Registra la Tarjeta',
        'tarjeta_salt',           // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'tarjeta_doc_ine_frente', // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'tarjeta_doc_ine_vuelta', // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'tarjeta_doc_carta',      // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'tarjeta_doc_tarjeta',    // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'tarjeta_razon',          // TEXT NULL DEFAULT NULL COMMENT 'Razón por la que no se Autoriza la Tarjeta' COLLATE 'latin1_swedish_ci',
        'tarjeta_status',         // TINYINT(1) NOT NULL DEFAULT '2' COMMENT '0 = Bloqueada, 1 = Activa, 2 = Por Activar, 9 = Eliminada',
    ];

    protected $casts = [
        'tarjeta_concesionario' => 'integer',
        'tarjeta_customer' => 'encrypted',
        'tarjeta_token' => 'encrypted',
        'tarjeta_identificador' => 'encrypted',
        'tarjeta_default' => 'boolean',
        'tarjeta_proveedor' => 'integer',
        'tarjeta_maximo' => 'float',
        'tarjeta_creada' => 'datetime',
    ];

    public function getIdAttribute()
    {
        return $this->tarjeta_id;
    }

    public function getUserIdAttribute()
    {
        return $this->tarjeta_concesionario;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'tarjeta_concesionario');
    }

    public function getCustomerAttribute()
    {
        return $this->tarjeta_customer;
    }

    public function getTokenAttribute()
    {
        return $this->tarjeta_token;
    }

    public function getIdentifierAttribute()
    {
        return $this->tarjeta_identificador;
    }

    public function getOwnerAttribute()
    {
        return $this->tarjeta_nombre;
    }

    public function getDigitsAttribute()
    {
        return $this->tarjeta_digitos;
    }

    public function getBrandAttribute()
    {
        return $this->tarjeta_marca;
    }

    public function getExpiresAtAttribute()
    {
        return $this->tarjeta_vigencia;
    }

    public function getBankAttribute()
    {
        return $this->tarjeta_banco;
    }

    public function getDefaultAttribute()
    {
        return $this->tarjeta_default;
    }

    public function getProviderIdAttribute()
    {
        return $this->tarjeta_proveedor;
    }

    public function provider()
    {
        return $this->belongsTo(CardProvider::class, 'tarjeta_proveedor');
    }

    public function getMaxAmountAttribute()
    {
        return $this->tarjeta_maximo;
    }

    public function getCreatedAtAttribute()
    {
        return $this->tarjeta_creada;
    }

    public function getSaltAttribute()
    {
        return $this->tarjeta_salt;
    }

    public function getDocIdFrontAttribute()
    {
        return $this->tarjeta_doc_ine_frente;
    }

    public function getDocIdBackAttribute()
    {
        return $this->tarjeta_doc_ine_vuelta;
    }

    public function getDocPermissionAttribute()
    {
        return $this->tarjeta_doc_carta;
    }

    public function getDocCardFrontAttribute()
    {
        return $this->tarjeta_doc_tarjeta;
    }

    public function getRejectedReasonAttribute()
    {
        return $this->tarjeta_razon;
    }

    public function getStatusAttribute()
    {
        return (object) [
            'id' => intval($this->tarjeta_status),
            'name' => ($this->tarjeta_status == 0 ? 'Bloqueada' : ($this->tarjeta_status == 1 ? 'Activa' : ($this->tarjeta_status == 2 ? 'Por Activar' : ($this->tarjeta_status == 9 ? 'Eliminada' : 'Inválida'))))
        ];
    }
}
