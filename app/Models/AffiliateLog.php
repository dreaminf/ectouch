<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AffiliateLog
 */
class AffiliateLog extends Model
{
    protected $table = 'affiliate_log';

    protected $primaryKey = 'log_id';

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'time',
        'user_id',
        'user_name',
        'money',
        'point',
        'separate_type'
    ];

    protected $guarded = [];

        
}