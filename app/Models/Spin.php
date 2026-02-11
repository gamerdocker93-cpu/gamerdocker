<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Spin extends Model
{
    protected $table = 'spins';

    protected $fillable = [
        'user_id','provider','game_code','status','request_id','request','result','error'
    ];

    protected $casts = [
        'request' => 'array',
        'result'  => 'array',
    ];
}
