<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserConfig extends Model
{
    public $timestamps = false;
    protected $table = 'concesionarios_configuracion';
    protected $primaryKey = 'config_id';

    protected $guarded = [];

    protected $appends = [
        'id',
        'user_id',
        'blocked',
        'search_transaction',
        'fee_tae',
        'fee_pins',
        'fee_services',
        'edit_email',
        'edit_mobile',
        'edit_company',
        'edit_password',
        'edit_phone',
        'mp_customer',
        'show_balance',
        'priority',
        'priority_amount',
        'region',
        'balance_tae',
        'balance_upfront',
        'balance_alert',
        'balance_alert_amount',
        'balance_services',
        'balance_pins',
        'ip_security',
        'token_security',
        'branch',
        'cards_permission',
        'last_purchase_date',
        'last_purchase_amount',
        'last_sale_date',
        'last_sale_amount',
        'mobile_sales',
    ];

    protected $hidden = [
        'config_bloqueado',
        'config_buscar_recarga',
        'config_comision',
        'config_comision_pines',
        'config_comision_servicios',
        'config_concesionario',
        'config_edicion_email',
        'config_edicion_movil',
        'config_edicion_negocio',
        'config_edicion_password',
        'config_edicion_telefono',
        'config_id',
        'config_mercado_pago_customer',
        'config_mostrar_saldo',
        'config_prioridad',
        'config_prioridad_cantidad',
        'config_region',
        'config_saldo',
        'config_saldo_adelantado',
        'config_saldo_minimo_aviso',
        'config_saldo_minimo_cantidad',
        'config_saldo_pds',
        'config_saldo_pines',
        'config_seguridad_ip',
        'config_seguridad_token',
        'config_sucursal',
        'config_tarjetas',
        'config_ultima_compra_fecha',
        'config_ultima_compra_monto',
        'config_ultima_venta_fecha',
        'config_ultima_venta_monto',
        'config_venta_movil',
    ];

    protected $casts = [
        'config_saldo' => 'float',
        'config_saldo_pds' => 'float',
        'config_saldo_pines' => 'float',
        'config_comision' => 'float',
        'config_comision_servicios' => 'float',
        'config_comision_pines' => 'float',
        'config_ultima_compra_monto' => 'float',
        'config_ultima_compra_fecha' => 'date',
        'config_ultima_venta_monto' => 'float',
        'config_ultima_venta_fecha' => 'date',
        'config_region' => 'integer',
        'config_saldo_minimo_cantidad' => 'float',
        'config_bloqueado' => 'boolean',
        'config_buscar_recarga' => 'boolean',
        'config_saldo_minimo_aviso' => 'boolean',
        'config_edicion_email' => 'boolean',
        'config_edicion_movil' => 'boolean',
        'config_edicion_negocio' => 'boolean',
        'config_edicion_password' => 'boolean',
        'config_edicion_telefono' => 'boolean',
        'config_mostrar_saldo' => 'boolean',
        'config_saldo_adelantado' => 'boolean',
        'config_seguridad_ip' => 'boolean',
        'config_seguridad_token' => 'boolean',
        'config_tarjetas' => 'boolean',
        'config_venta_movil' => 'boolean',
    ];

    protected $attributes = [
        'config_saldo' => 0,
        'config_saldo_pds' => 0,
        'config_saldo_pines' => 0,
        'config_tarjetas' => 0,
    ];

    public function getIdAttribute()
    {
        return $this->config_id;
    }

    public function getUserIdAttribute()
    {
        return $this->config_concesionario;
    }

    public function getBlockedAttribute()
    {
        return $this->config_bloqueado;
    }

    public function getSearchTransactionAttribute()
    {
        return $this->config_buscar_recarga;
    }

    public function getFeeTaeAttribute()
    {
        return $this->config_comision;
    }

    public function getFeePinsAttribute()
    {
        return $this->config_comision_pines;
    }

    public function getFeeServicesAttribute()
    {
        return $this->config_comision_servicios;
    }

    public function getEditEmailAttribute()
    {
        return $this->config_edicion_email;
    }

    public function getEditMobileAttribute()
    {
        return $this->config_edicion_movil;
    }

    public function getEditCompanyAttribute()
    {
        return $this->config_edicion_negocio;
    }

    public function getEditPasswordAttribute()
    {
        return $this->config_edicion_password;
    }

    public function getEditPhoneAttribute()
    {
        return $this->config_edicion_telefono;
    }

    public function getMpCustomerAttribute()
    {
        return $this->config_mercado_pago_customer;
    }

    public function getShowBalanceAttribute()
    {
        return $this->config_mostrar_saldo;
    }

    public function getPriorityAttribute()
    {
        return $this->config_prioridad;
    }

    public function getPriorityAmountAttribute()
    {
        return $this->config_prioridad_cantidad;
    }

    public function getRegionAttribute()
    {
        return $this->config_region;
    }

    public function getBalanceTaeAttribute()
    {
        return $this->config_saldo;
    }

    public function getBalanceUpfrontAttribute()
    {
        return $this->config_saldo_adelantado;
    }

    public function getBalanceAlertAttribute()
    {
        return $this->config_saldo_minimo_aviso;
    }

    public function getBalanceAlertAmountAttribute()
    {
        return $this->config_saldo_minimo_cantidad;
    }

    public function getBalanceServicesAttribute()
    {
        return $this->config_saldo_pds;
    }

    public function getBalancePinsAttribute()
    {
        return $this->config_saldo_pines;
    }

    public function getIpSecurityAttribute()
    {
        return $this->config_seguridad_ip;
    }

    public function getTokenSecurityAttribute()
    {
        return $this->config_seguridad_token;
    }

    public function getBranchAttribute()
    {
        return $this->config_sucursal;
    }

    public function getCardsPermissionAttribute()
    {
        return $this->config_tarjetas;
    }

    public function getLastPurchaseDateAttribute()
    {
        return $this->config_ultima_compra_fecha;
    }

    public function getLastPurchaseAmountAttribute()
    {
        return $this->config_ultima_compra_monto;
    }

    public function getLastSaleDateAttribute()
    {
        return $this->config_ultima_venta_fecha;
    }

    public function getLastSaleAmountAttribute()
    {
        return $this->config_ultima_venta_monto;
    }

    public function getMobileSalesAttribute()
    {
        return $this->config_venta_movil;
    }
}
