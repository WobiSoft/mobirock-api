<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostView extends Model
{
    public $timestamps = false;
    protected $table = 'noticias_vistas';
    protected $primaryKey = 'vista_id';

    protected $guarded = [];

    protected $appends = [
        'id',
        'post_id',
        'user_id',
        'viewed_at',
        'status',
    ];

    protected $hidden = [
        'vista_id',            // BIGINT(20) NOT NULL AUTO_INCREMENT,
        'vista_noticia',       // BIGINT(20) NOT NULL COMMENT 'ID de la Noticia',
        'vista_concesionario', // BIGINT(20) NOT NULL COMMENT 'ID del Concesionario que visito la noticia',
        'vista_realizada',     // DATETIME NULL DEFAULT NULL,
        'vista_status',        // TINYINT(1) NOT NULL DEFAULT '0' COMMENT '0 = No leida, 1 = Leida, 2 = No leida en vigencia',
    ];

    protected $casts = [
        'vista_noticia' => 'integer',
        'vista_concesionario' => 'integer',
        'vista_realizada' => 'datetime',
        'vista_status' => 'boolean',
    ];

    public function getIdAttribute()
    {
        return $this->vista_id;
    }

    public function getPostIdAttribute()
    {
        return $this->vista_noticia;
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'vista_noticia');
    }

    public function getUserIdAttribute()
    {
        return $this->vista_concesionario;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'vista_concesionario');
    }

    public function getViewedAtAttribute()
    {
        return $this->vista_realizada;
    }

    public function getStatusAttribute()
    {
        return $this->vista_status;
    }
}
