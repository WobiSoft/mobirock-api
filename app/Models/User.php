<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public $timestamps = false;
    protected $table = 'concesionarios';
    protected $primaryKey = 'concesionario_id';

    protected $guarded = [];

    protected $appends = [
        'id',
        'parent_id',
        'type_id',
        'username',
        'first_name',
        'second_name',
        'first_surname',
        'second_surname',
        'name',
        'full_name',
        'email',
        'mobile',
        'phone',
        'birthday',
        'created_by_id',
        'created_at',
        'status',
        'priority',
        'priority_amount',
    ];

    protected $hidden = [
        'concesionario_id',                 // BIGINT(20) NOT NULL AUTO_INCREMENT,
        'concesionario_padre',              // BIGINT(20) NULL DEFAULT NULL COMMENT 'ID del Concesionario que es Padre de este Usuario',
        'concesionario_numero',             // VARCHAR(12) NULL DEFAULT NULL COMMENT 'Número Único de 8 Dígitos que identifica a un Concesionario en la aplicacion' COLLATE 'latin1_swedish_ci',
        'concesionario_username',           // VARCHAR(255) NULL DEFAULT NULL COMMENT 'Nombre de Concesionario para ingresar a la aplicación' COLLATE 'latin1_swedish_ci',
        'concesionario_password',           // VARCHAR(60) NULL DEFAULT NULL COMMENT 'Contraseña para ingresar a la aplicación' COLLATE 'latin1_swedish_ci',
        'concesionario_tipo',               // BIGINT(20) NULL DEFAULT NULL COMMENT '1 = Master, 2 = Usuario Master, 3 = Distribuidor, 4 = Usuario Distribuidor, 5 = PDV, 6 = Cajero',
        'concesionario_primer_nombre',      // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'concesionario_segundo_nombre',     // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'concesionario_apellido_paterno',   // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'concesionario_apellido_materno',   // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'concesionario_email',              // TEXT NULL DEFAULT NULL COMMENT 'Email único para envío de Correos' COLLATE 'latin1_swedish_ci',
        'concesionario_movil',              // TEXT NULL DEFAULT NULL COMMENT 'Teléfono Celular único para envío de SMS y acciones relacionadas con la seguridad' COLLATE 'latin1_swedish_ci',
        'concesionario_telefono',           // TEXT NULL DEFAULT NULL COMMENT 'Opcional, en caso de contar con él' COLLATE 'latin1_swedish_ci',
        'concesionario_fecha_nacimiento',   // DATE NULL DEFAULT NULL,
        'concesionario_creado_por',         // BIGINT(20) NULL DEFAULT NULL COMMENT 'ID del Concesionario que creó a este',
        'concesionario_creado',             // TIMESTAMP NULL DEFAULT current_timestamp() COMMENT 'Fecha y hora de creación del Concesionario',
        'concesionario_status',             // TINYINT(1) NULL DEFAULT '2' COMMENT '0 = Eliminado, 1 = Activo, 2 = Esperando Activación',
        'concesionario_prioridad',          // INT(11) NULL DEFAULT '0',
        'concesionario_prioridad_cantidad', // DECIMAL(12,2) NULL DEFAULT '0.00',
    ];

    protected $casts = [
        'concesionario_padre' => 'integer',
        'concesionario_fecha_nacimiento' => 'date',
        'concesionario_creado' => 'datetime',
        'concesionario_prioridad_cantidad' => 'float'
    ];

    public function business()
    {
        return $this->hasOne(Business::class, 'negocio_concesionario');
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'concesionario_padre');
    }

    public function type()
    {
        return $this->belongsTo(UserType::class, 'concesionario_tipo');
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'concesionario_creado_por');
    }

    public function config()
    {
        return $this->hasOne(UserConfig::class, 'config_concesionario');
    }

    public function otps()
    {
        return $this->hasMany(UserOtp::class, 'codigo_concesionario');
    }

    public function edits()
    {
        return $this->hasMany(UserEdit::class, 'edicion_concesionario');
    }

    public function getNameAttribute()
    {
        return "{$this->concesionario_primer_nombre} {$this->concesionario_apellido_paterno}";
    }

    public function getFullNameAttribute()
    {
        return $this->concesionario_primer_nombre . ($this->concesionario_segundo_nombre ? (' ' . $this->concesionario_segundo_nombre) : '') . ' ' . $this->concesionario_apellido_paterno . ($this->concesionario_apellido_materno ? (' ' . $this->concesionario_apellido_materno) : '');
    }

    public function getIdAttribute()
    {
        return $this->concesionario_id;
    }

    public function getParentIdAttribute()
    {
        return $this->concesionario_padre;
    }

    public function getUsernameAttribute()
    {
        return $this->concesionario_numero;
    }

    public function getPasswordAttribute()
    {
        return $this->concesionario_password;
    }

    public function getTypeIdAttribute()
    {
        return $this->concesionario_tipo;
    }

    public function getFirstNameAttribute()
    {
        return $this->concesionario_primer_nombre;
    }

    public function getSecondNameAttribute()
    {
        return $this->concesionario_segundo_nombre;
    }

    public function getFirstSurnameAttribute()
    {
        return $this->concesionario_apellido_paterno;
    }

    public function getSecondSurnameAttribute()
    {
        return $this->concesionario_apellido_materno;
    }

    public function getEmailAttribute()
    {
        return $this->concesionario_email;
    }

    public function getMobileAttribute()
    {
        return $this->concesionario_movil;
    }

    public function getPhoneAttribute()
    {
        return $this->concesionario_telefono;
    }

    public function getBirthdayAttribute()
    {
        return $this->concesionario_fecha_nacimiento;
    }

    public function getCreatedByIdAttribute()
    {
        return $this->concesionario_creado_por;
    }

    public function getCreatedAtAttribute()
    {
        return $this->concesionario_creado;
    }

    public function getStatusAttribute()
    {
        return (object) [
            'id' => intval($this->concesionario_status),
            'name' => ($this->concesionario_status == 0 ? 'Eliminado' : ($this->concesionario_status == 1 ? 'Activo' : ($this->concesionario_status == 2 ? 'Esperando Activación' : 'Inválido')))
        ];
    }

    public function getPriorityAttribute()
    {
        return $this->concesionario_prioridad;
    }

    public function getPriorityAmountAttribute()
    {
        return $this->concesionario_prioridad_cantidad;
    }

    public function able()
    {
        if (!$this)
        {
            return (object) [
                'status' => false,
                'message' => 'Este No. de Usuario no existe.',
                'code' => 404
            ];
        }

        if ($this->config->blocked)
        {
            return (object) [
                'status' => false,
                'message' => 'Por el momento tu acceso esta bloqueado. Comunícate con tu distribuidor para aclarar esta situacion.',
                'code' => 401
            ];
        }

        if ($this->status->id === 2)
        {
            return (object) [
                'status' => false,
                'message' => 'Debes activar tu cuenta para acceder a la aplicación.',
                'code' => 403
            ];
        }

        return (object) [
            'status' => true
        ];
    }

    public function canCommunicate()
    {
        return (object) [
            'status' => ($this->email || $this->mobile ? true : false),
            'type' => ($this->email && $this->mobile ? 'both' : ($this->email ? 'email' : ($this->mobile ? 'mobile' : 'none')))
        ];
    }

    public function removeOtps()
    {
        $unusedOtps = $this->otps()->whereCodigoStatus(0)->get();

        $unusedOtps->each(function ($otp)
        {
            $otp->delete();
        });
    }

    public function createOtp()
    {
        $this->removeOtps();

        $otp = rand(100000, 999999);

        UserOtp::create([
            'codigo_concesionario' => $this->id,
            'codigo_digitos'       => '0000',
            'codigo_uuid'          => password_hash($otp, PASSWORD_BCRYPT),
            'codigo_creado'        => date('Y-m-d H:i:s'),
            'codigo_status'        => 0
        ]);

        return $otp;
    }

    public function currentOtp()
    {
        return $this->otps()->whereCodigoStatus(0)->orderBy('codigo_id', 'DESC')->first();
    }

    public function getParent()
    {
        if (in_array($this->type->id, [2, 4, 6, 8, 10]))
        {
            return $this->parent->parent->id;
        }
        else
        {
            return $this->parent->id;
        }
    }

    public function uniqueUsername()
    {
        $username = rand(10000000, 99999999);

        $exists = User::where('concesionario_numero', $username)->exists();

        while ($exists)
        {
            $username = rand(10000000, 99999999);
            $exists = User::where('concesionario_numero', $username)->exists();
        }

        return $username;
    }

    public function saveProfileState($user, $business)
    {
        $currentData = [
            'user' => [
                'email'          => $this->email,
                'first_name'     => $this->first_name,
                'first_surname'  => $this->first_surname,
                'mobile'         => $this->mobile,
                'phone'          => $this->phone,
                'second_name'    => $this->second_name,
                'second_surname' => $this->second_surname,
            ],
            'business' => in_array($this->type->id, [1, 3, 5, 7, 8]) ? [
                'name'             => $this->business->name,
                'apartment_number' => $this->business->address->apartment_number ?? NULL,
                'complement_1'     => $this->business->address->complement_1 ?? NULL,
                'complement_2'     => $this->business->address->complement_2 ?? NULL,
                'locality'         => $this->business->address->locality ?? NULL,
                'municipality'     => $this->business->address->municipality ?? NULL,
                'postal_code'      => $this->business->address->postal_code ?? NULL,
                'settlement'       => $this->business->address->settlement ?? NULL,
                'state'            => $this->business->address->state ?? NULL,
                'street'           => $this->business->address->street ?? NULL,
                'street_number'    => $this->business->address->street_number ?? NULL,
            ] : NULL
        ];

        $newData = ['user' => $user, 'business' => $business];

        $this->edits()->create([
            'edicion_concesionario' => $this->id,
            'edicion_valor_anterior' => $currentData,
            'edicion_valor_nuevo' => $newData,
            'edicion_solicitada' => date('Y-m-d H:i:s'),
            'edicion_realizada' => date('Y-m-d H:i:s'),
            'edicion_codigo' => '0000',
            'edicion_tipo' => 9,
            'edicion_status' => 1,
        ]);
    }
}
