<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    public $timestamps = false;
    protected $table = 'concesionarios_compras';
    protected $primaryKey = 'compra_id';

    protected $guarded = [];

    protected $appends = [
        'id',
        'sender_id',
        'receiver_id',
        'subtotal',
        'discount',
        'vat',
        'total',
        'account_id',
        'method_id',
        'identifier',
        'paid_at',
        'registered_at',
        'settled_at',
        'deposited_at',
        'comments',
        'deleted_reason',
        'product',
        'conditions',
        'status',
    ];

    protected $hidden = [
        'compra_id',            // BIGINT(20) NOT NULL AUTO_INCREMENT,
        'compra_origen',        // BIGINT(20) NOT NULL COMMENT 'ID del Concesionario que vende',
        'compra_destino',       // BIGINT(20) NOT NULL COMMENT 'ID del Concesionario que compra',
        'compra_subtotal',      // DECIMAL(20,2) NOT NULL,
        'compra_descuento',     // DECIMAL(20,2) NULL DEFAULT NULL,
        'compra_iva',           // DECIMAL(20,2) NOT NULL,
        'compra_total',         // DECIMAL(20,2) NOT NULL,
        'compra_cuenta',        // BIGINT(20) NULL DEFAULT NULL COMMENT 'ID de la Cuenta de esta Compra',
        'compra_forma',         // BIGINT(20) NULL DEFAULT NULL COMMENT 'ID de la Forma de Pago de esta Compra',
        'compra_producto',      // TINYINT(1) NULL DEFAULT '1' COMMENT '1 = TAE, 2 = PDS',
        'compra_cdr',           // TEXT NULL DEFAULT NULL COMMENT 'Código de Rastreo' COLLATE 'latin1_swedish_ci',
        'compra_realizada',     // DATETIME NULL DEFAULT NULL COMMENT 'Fecha en que se Solicitó',
        'compra_registrada',    // TIMESTAMP NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha en que se Registró',
        'compra_liquidada',     // DATE NULL DEFAULT NULL COMMENT 'Fecha en que se Liquidó',
        'compra_condiciones',   // TINYINT(1) NOT NULL DEFAULT '1' COMMENT '1 = Contado, 2 = Crédito',
        'compra_abonado',       // DECIMAL(12,2) NOT NULL DEFAULT '0.00' COMMENT 'En caso de que sea un Crédito',
        'compra_observaciones', // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'compra_razon',         // TEXT NULL DEFAULT NULL COMMENT 'Razón de la Eliminación' COLLATE 'latin1_swedish_ci',
        'compra_status',        // TINYINT(1) NOT NULL DEFAULT '1' COMMENT '0 = Eliminada, 1 = Pagada, 2 = Por Pagar',
    ];

    protected $casts = [
        'compra_origen' => 'integer',
        'compra_destino' => 'integer',
        'compra_cuenta' => 'integer',
        'compra_forma' => 'integer',
        'compra_subtotal' => 'float',
        'compra_descuento' => 'float',
        'compra_iva' => 'float',
        'compra_total' => 'float',
        'compra_abonado' => 'float',
        'compra_realizada' => 'datetime',
        'compra_registrada' => 'datetime',
        'compra_liquidada' => 'date'
    ];

    public function get_id_attribute()
    {
        return $this->compra_id;
    }

    public function get_sender_id_attribute()
    {
        return $this->compra_origen;
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'compra_origen');
    }

    public function get_receiver_id_attribute()
    {
        return $this->compra_destino;
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'compra_destino');
    }

    public function get_subtotal_attribute()
    {
        return $this->compra_subtotal;
    }

    public function get_discount_attribute()
    {
        return $this->compra_descuento;
    }

    public function get_vat_attribute()
    {
        return $this->compra_iva;
    }

    public function get_total_attribute()
    {
        return $this->compra_total;
    }

    public function get_account_id_attribute()
    {
        return $this->compra_cuenta;
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'compra_cuenta');
    }

    public function get_method_id_attribute()
    {
        return $this->compra_forma;
    }

    public function method()
    {
        return $this->belongsTo(Method::class, 'compra_forma');
    }

    public function get_identifier_attribute()
    {
        return $this->compra_cdr;
    }

    public function get_paid_at_attribute()
    {
        return $this->compra_realizada;
    }

    public function get_registered_at_attribute()
    {
        return $this->compra_registrada;
    }

    public function get_settled_at_attribute()
    {
        return $this->compra_liquidada;
    }

    public function get_deposited_at_attribute()
    {
        return $this->compra_abonado;
    }

    public function get_comments_attribute()
    {
        return $this->compra_observaciones;
    }

    public function get_deleted_reason_attribute()
    {
        return $this->compra_razon;
    }

    public function get_product_attribute()
    {
        return (object) [
            'id' => intval($this->compra_producto),
            'name' => ($this->compra_producto == 1 ? 'Tiempo Aire Electrónico' : ($this->compra_producto == 2 ? 'Pago de Servicios' : 'Inválido'))
        ];
    }

    public function get_conditions_attribute()
    {
        return (object) [
            'id' => intval($this->compra_condiciones),
            'name' => ($this->compra_condiciones == 1 ? 'Contado' : ($this->compra_condiciones == 2 ? 'Crédito' : 'Inválido'))
        ];
    }

    public function get_status_attribute()
    {
        return (object) [
            'id' => intval($this->compra_status),
            'name' => ($this->compra_status == 0 ? 'Eliminada' : ($this->compra_status == 1 ? 'Pagada' : ($this->compra_status == 2 ? 'Por Pagar' : 'Inválida')))
        ];
    }
}
