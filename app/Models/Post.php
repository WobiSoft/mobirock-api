<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    public $timestamps = false;
    protected $table = 'noticias';
    protected $primaryKey = 'noticia_id';

    protected $guarded = [];

    protected $appends = [
        'id',
        'user_id',
        'level',
        'title',
        'content',
        'excerpt',
        'cover',
        'created_at',
        'created_by_id',
        'published_at',
        'published_by_id',
        'modified_at',
        'expires_at',
        'views',
        'deleted_reason',
        'status',
    ];

    protected $hidden = [
        'noticia_id',            // BIGINT(20) NOT NULL AUTO_INCREMENT,
        'noticia_concesionario', // BIGINT(20) NOT NULL COMMENT 'ID del Concesionario que creó la Noticia',
        'noticia_nivel',         // TINYINT(1) NOT NULL DEFAULT '1' COMMENT '1 = Para todos, 2 = Para PDVs, 3 = Para Distris',
        'noticia_titulo',        // VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
        'noticia_contenido',     // LONGTEXT NOT NULL COLLATE 'latin1_swedish_ci',
        'noticia_extracto',      // VARCHAR(255) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'noticia_imagen',        // TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
        'noticia_creada',        // TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        'noticia_creada_por',    // BIGINT(20) NOT NULL COMMENT 'ID del Usuario que creó la Noticia',
        'noticia_publicada',     // DATETIME NULL DEFAULT NULL,
        'noticia_publicada_por', // BIGINT(20) NULL DEFAULT NULL COMMENT 'ID del Concesionario que Publicó la Noticia',
        'noticia_modificada',    // DATETIME NULL DEFAULT NULL,
        'noticia_vigencia',      // DATETIME NULL DEFAULT NULL,
        'noticia_vistas',        // INT(10) NOT NULL DEFAULT '0',
        'noticia_razon',         // TEXT NULL DEFAULT NULL COMMENT 'Razón por la cual fue eliminada si el estatus es 5' COLLATE 'latin1_swedish_ci',
        'noticia_status',        // TINYINT(1) NOT NULL DEFAULT '1' COMMENT '0  = No Publicada, 1 = Publicada, 5 = Eliminada',
    ];

    protected $casts = [
        'noticia_concesionario' => 'integer',
        'noticia_creada_por' => 'integer',
        'noticia_publicada_por' => 'integer',
        'noticia_creada' => 'datetime',
        'noticia_publicada' => 'datetime',
        'noticia_modificada' => 'datetime',
        'noticia_vigencia' => 'datetime',
        'noticia_vistas' => 'integer',
    ];

    public function getIdAttribute()
    {
        return $this->noticia_id;
    }

    public function getUserIdAttribute()
    {
        return $this->noticia_concesionario;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'noticia_concesionario');
    }

    public function getLevelAttribute()
    {
        return (object) [
            'id' => intval($this->noticia_nivel),
            'name' => ($this->noticia_nivel === 1 ? 'Toda la RED' : ($this->noticia_nivel === 2 ? 'Para Puntos de Venta' : ($this->noticia_nivel == 3 ? 'Para Distribuidores' : 'Inválido'))),
        ];
    }

    public function getTitleAttribute()
    {
        return $this->noticia_titulo;
    }

    public function getContentAttribute()
    {
        return $this->noticia_contenido;
    }

    public function getExcerptAttribute()
    {
        return $this->noticia_extracto;
    }

    public function getCoverAttribute()
    {
        return $this->noticia_imagen;
    }

    public function getCreatedAtAttribute()
    {
        return $this->noticia_creada;
    }

    public function getCreatedByIdAttribute()
    {
        return $this->noticia_creada_por;
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'noticia_creada_por');
    }

    public function getPublishedAtAttribute()
    {
        return $this->noticia_publicada;
    }

    public function getPublishedByIdAttribute()
    {
        return $this->noticia_publicada_por;
    }

    public function published_by()
    {
        return $this->belongsTo(User::class, 'noticia_publicada_por');
    }

    public function getModifiedAtAttribute()
    {
        return $this->noticia_modificada;
    }

    public function getExpiresAtAttribute()
    {
        return $this->noticia_vigencia;
    }

    public function getViewsAttribute()
    {
        return $this->noticia_vistas;
    }

    public function getDeletedReasonAttribute()
    {
        return $this->noticia_razon;
    }

    public function getStatusAttribute()
    {
        return (object) [
            'id' => intval($this->noticia_status),
            'name' => ($this->noticia_status == 0 ? 'No Publicada' : ($this->noticia_status == 1 ? 'Publicada' : ($this->noticia_status == 5 ? 'Eliminada' : 'Inválida')))
        ];
    }
}
