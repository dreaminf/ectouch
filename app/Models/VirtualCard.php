<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class VirtualCard
 */
class VirtualCard extends Model
{
    protected $table = 'virtual_card';

    protected $primaryKey = 'card_id';

    public $timestamps = false;

    protected $fillable = [
        'goods_id',
        'card_sn',
        'card_password',
        'add_date',
        'end_date',
        'is_saled',
        'order_sn',
        'crc32'
    ];

    protected $guarded = [];

        
}