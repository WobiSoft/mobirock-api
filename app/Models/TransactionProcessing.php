<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionProcessing extends Model
{
    protected $guarded = [];

    protected $appends = [];

    protected $hidden = [];

    protected $casts = [
        'user_id' => 'integer',
        'provider_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
