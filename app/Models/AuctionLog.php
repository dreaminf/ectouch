<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AuctionLog
 */
class AuctionLog extends Model
{
    protected $table = 'auction_log';

    protected $primaryKey = 'log_id';

    public $timestamps = false;

    protected $fillable = [
        'act_id',
        'bid_user',
        'bid_price',
        'bid_time'
    ];

    protected $guarded = [];

        
}