<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Method extends Model
{
    public $timestamps = false;
    protected $table = 'formas';
    protected $primaryKey = 'forma_id';

    protected $guarded = [];

    protected $appends = [
        'id',
        'name',
        'description',
        'status',
    ];

    protected $hidden = [
        'forma_id',          // BIGINT(20) NOT NULL AUTO_INCREMENT,
        'forma_nombre',      // VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
        'forma_descripcion', // TEXT NOT NULL COLLATE 'latin1_swedish_ci',
        'forma_status',      // TINYINT(1) NOT NULL COMMENT '0 = Desactivada, 1 = Activada',
    ];

    protected $casts = [
        'forma_status' => 'boolean',
    ];

    public function accounts()
    {
        return $this->hasMany(Account::class, 'cuenta_forma');
    }

    public function getIdAttribute()
    {
        return $this->forma_id;
    }

    public function getNameAttribute()
    {
        return $this->forma_nombre;
    }

    public function getDescriptionAttribute()
    {
        return $this->forma_descripcion;
    }

    public function getStatusAttribute()
    {
        return $this->forma_status;
    }
}
