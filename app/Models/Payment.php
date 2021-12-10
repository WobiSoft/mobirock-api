<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    public $timestamps = false;
    protected $table = 'pagos';
    protected $primaryKey = 'pago_id';

    protected $guarded = [];

    protected $appends = [
        'id',
        'user_id',
        'method_id',
        'account_id',
        'transaction_id',
        'amount',
        'type',
        'product',
        'identifier',
        'registered_by_id',
        'registered_at',
        'date',
        'verified_at',
        'audited_at',
        'processed_at',
        'rejected_at',
        'rejected_reason',
        'editied_reason',
        'comments',
        'receipt',
        'invoicing',
        'child_payment_id',
        'status',
    ];

    protected $hidden = [
        'pago_id',             // BIGINT(20) NOT NULL AUTO_INCREMENT,
        'pago_concesionario',  // BIGINT(20) NOT NULL COMMENT 'ID del Concesionario que realizó el Pago',
        'pago_forma',          // BIGINT(20) NULL DEFAULT NULL COMMENT 'ID de la Forma de Pago',
        'pago_cuenta',         // BIGINT(20) NULL DEFAULT NULL COMMENT 'ID de la Cuenta de Pago',
        'pago_transaccion',    // BIGINT(20) NULL DEFAULT NULL COMMENT 'ID de la Transacción asignada a este Pago',
        'pago_monto',          // DECIMAL(20,2) NULL DEFAULT NULL COMMENT 'Monto Bruto del Pago',
        'pago_tipo',           // TINYINT(1) NULL DEFAULT NULL COMMENT '1 = Contado, 2 = Liquidación de Crédito, 3 = Abono Crédito',
        'pago_producto',       // TINYINT(1) NULL DEFAULT '1' COMMENT '1 = TAE, 2 = PDS',
        'pago_cdr',            // VARCHAR(100) NULL DEFAULT NULL COMMENT 'Código de Rastreo (ej. txn_id para PayPal, Autorización para Deposito, etc.)' COLLATE 'latin1_swedish_ci',
        'pago_registrado_por', // BIGINT(20) UNSIGNED NULL DEFAULT NULL,
        'pago_registrado',     // TIMESTAMP NULL DEFAULT current_timestamp() COMMENT 'Fecha y Hora en que se registro el Pago en REDPrepaid',
        'pago_fecha',          // DATE NULL DEFAULT NULL COMMENT 'Fecha en que se Realizó el Pago en la Entidad Bancaria o Servicio Electrónico',
        'pago_verificado',     // DATETIME NULL DEFAULT NULL COMMENT 'Fecha y Hora en que se Verificó el Pago por parte de REDPrepaid',
        'pago_auditado',       // DATETIME NULL DEFAULT NULL COMMENT 'Fecha y Hora en que se Auditó el Pago por parte de REDPrepaid',
        'pago_procesado',      // DATETIME NULL DEFAULT NULL COMMENT 'Fecha y Hora en que se Aplico el Pago',
        'pago_rechazado',      // DATETIME NULL DEFAULT NULL COMMENT 'Fecha y Hora en que se Rechazó el Pago por parte de REDPrepaid',
        'pago_rechazado_razon', // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'pago_razon_edicion',  // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'pago_observaciones',  // TEXT NULL DEFAULT NULL COMMENT 'En caso de ser necesaria' COLLATE 'latin1_swedish_ci',
        'pago_comprobante',    // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'pago_facturar',       // TINYINT(4) NULL DEFAULT '0' COMMENT '0 = No Facturar, 1 = Facturar, 2 = Facturada',
        'pago_hijo',           // BIGINT(20) NULL DEFAULT NULL COMMENT 'ID del Pago que genero este Pago, solo en caso de Distribuidores Autoadministrables',
        'pago_status',         // TINYINT(1) NOT NULL DEFAULT '2' COMMENT '0 = Rechazado, 1 = Aplicado, 2 = Recibido, 3 = Verificado, 4 = Auditado, 5 = Requiere Verificación',
    ];

    protected $attributes = [
        'pago_tipo' => 1,
        'pago_producto' => 1,
        'pago_facturar' => 0,
        'pago_status' => 2,
    ];

    protected $casts = [
        'pago_concesionario' => 'integer',
        'pago_forma' => 'integer',
        'pago_cuenta' => 'integer',
        'pago_transaccion' => 'integer',
        'pago_registrado_por' => 'integer',
        'pago_hijo' => 'integer',
        'pago_monto' => 'float',
        'pago_registrado' => 'datetime',
        'pago_fecha' => 'date',
        'pago_verificado' => 'datetime',
        'pago_auditado' => 'datetime',
        'pago_procesado' => 'datetime',
        'pago_rechazado' => 'datetime',
    ];

    public function getIdAttribute()
    {
        return $this->pago_id;
    }

    public function getUserIdAttribute()
    {
        return $this->pago_concesionario;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'pago_concesionario');
    }

    public function getMethodIdAttribute()
    {
        return $this->pago_forma;
    }

    public function method()
    {
        return $this->belongsTo(Method::class, 'pago_forma');
    }

    public function getAccountIdAttribute()
    {
        return $this->pago_cuenta;
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'pago_cuenta');
    }

    public function getTransactionIdAttribute()
    {
        return $this->pago_transaccion;
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'pago_transaccion');
    }

    public function getAmountAttribute()
    {
        return $this->pago_monto;
    }

    public function getTypeAttribute()
    {
        return $this->pago_tipo;
    }

    public function getProductAttribute()
    {
        return $this->pago_producto;
    }

    public function getIdentifierAttribute()
    {
        return $this->pago_cdr;
    }

    public function getRegisteredByIdAttribute()
    {
        return $this->pago_registrado_por;
    }

    public function registered_by()
    {
        return $this->belongsTo(User::class, 'pago_registrado_por');
    }

    public function getRegisteredAtAttribute()
    {
        return $this->pago_registrado;
    }

    public function getDateAttribute()
    {
        return $this->pago_fecha;
    }

    public function getVerifiedAtAttribute()
    {
        return $this->pago_verificado;
    }

    public function getAuditedAtAttribute()
    {
        return $this->pago_auditado;
    }

    public function getProcessedAtAttribute()
    {
        return $this->pago_procesado;
    }

    public function getRejectedAtAttribute()
    {
        return $this->pago_rechazado;
    }

    public function getRejectedReasonAttribute()
    {
        return $this->pago_rechazado_razon;
    }

    public function getEditiedReasonAttribute()
    {
        return $this->pago_razon_edicion;
    }

    public function getCommentsAttribute()
    {
        return $this->pago_observaciones;
    }

    public function getReceiptAttribute()
    {
        return $this->pago_comprobante;
    }

    public function getInvoicingAttribute()
    {
        return (object) [
            'id' => intval($this->pago_facturar),
            'name' => ($this->pago_facturar == 0 ? 'No Facturar' : ($this->pago_facturar == 1 ? 'Facturar' : ($this->pago_facturar == 2 ? 'Facturada' : 'Inválido')))
        ];
    }

    public function getChildPaymentIdAttribute()
    {
        return $this->pago_hijo;
    }

    public function child_payment()
    {
        return $this->belongsTo(Payment::class, 'pago_hijo');
    }

    public function getStatusAttribute()
    {
        return (object) [
            'id' => intval($this->pago_status),
            'name' => ($this->pago_status == 0 ? 'Rechazada' : ($this->pago_status == 1 ? 'Aplicada' : ($this->pago_status == 2 ? 'Recibida' : ($this->pago_status == 3 ? 'Verificada' : ($this->pago_status == 4 ? 'Auditada' : ($this->pago_status == 5 ? 'Requiere Verificación' : 'Inválido'))))))
        ];
    }

    public function checkDuplicated()
    {
        return Payment::wherePagoConcesionario($this->pago_concesionario)
            ->where('pago_cdr', 'like', "%{$this->pago_cdr}%")
            ->wherePagoCuenta($this->pago_cuenta)
            ->wherePagoForma($this->pago_forma)
            ->wherePagoMonto($this->pago_monto)
            ->wherePagoFecha($this->pago_fecha)
            ->where('pago_status', '!=', 0)
            ->exists();
    }

    public function checkIdentifier()
    {
        $identifier = $this->pago_cdr;

        $payment = Payment::select(['pago_cdr'])->where('pago_cdr', 'like', "%{$this->pago_cdr}-%")->orderBy('pago_id', 'DESC')->first();

        if ($payment && strpos($payment->pago_cdr, '-R') !== false)
        {
            $explodedIdentifier = explode('-', $payment->pago_cdr);
            $existingIdentifier = end($explodedIdentifier);
            $times = intval(str_replace('R', '', $existingIdentifier));
            $times++;
            $identifier = "{$this->identifier}-R{$times}";
        }

        return $identifier;
    }
}
