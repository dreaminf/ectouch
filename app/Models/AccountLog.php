<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AccountLog
 */
class AccountLog extends Model
{
    protected $table = 'account_log';

    protected $primaryKey = 'log_id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_money',
        'frozen_money',
        'rank_points',
        'pay_points',
        'change_time',
        'change_desc',
        'change_type'
    ];

    protected $guarded = [];

        
}