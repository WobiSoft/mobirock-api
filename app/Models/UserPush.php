<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPush extends Model
{
    public $timestamps = false;
    protected $table = 'push_tokens';

    protected $guarded = [];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'status' => 'boolean',
    ];

    protected $attributes = [
        'status' => 1
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
