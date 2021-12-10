<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    public $timestamps = false;
    protected $table = 'bancos';
    protected $primaryKey = 'banco_id';

    protected $guarded = [];

    protected $appends = [
        'id',
        'company',
        'name',
        'status',
    ];

    protected $hidden = [
        'banco_id',           // BIGINT(20) NOT NULL AUTO_INCREMENT,
        'banco_razon_social', // VARCHAR(50) NOT NULL COLLATE 'latin1_swedish_ci',
        'banco_nombre',       // VARCHAR(30) NOT NULL COLLATE 'latin1_swedish_ci',
        'banco_status',       // TINYINT(1) NOT NULL,
    ];

    protected $casts = [
        'banco_status' => 'boolean',
    ];

    public function getIdAttribute()
    {
        return $this->banco_id;
    }

    public function getCompanyAttribute()
    {
        return $this->banco_razon_social;
    }

    public function getNameAttribute()
    {
        return $this->banco_nombre;
    }

    public function getStatusAttribute()
    {
        return $this->banco_status;
    }
}
