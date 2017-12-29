<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Sessions
 */
class Sessions extends Model
{
    protected $table = 'sessions';

    protected $primaryKey = 'sesskey';

    public $timestamps = false;

    protected $fillable = [
        'expiry',
        'userid',
        'adminid',
        'ip',
        'user_name',
        'user_rank',
        'discount',
        'email',
        'data'
    ];

    protected $guarded = [];

        
}