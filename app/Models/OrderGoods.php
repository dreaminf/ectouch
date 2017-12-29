<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderGoods
 */
class OrderGoods extends Model
{
    protected $table = 'order_goods';

    protected $primaryKey = 'rec_id';

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'goods_id',
        'goods_name',
        'goods_sn',
        'product_id',
        'goods_number',
        'market_price',
        'goods_price',
        'goods_attr',
        'send_number',
        'is_real',
        'extension_code',
        'parent_id',
        'is_gift',
        'goods_attr_id'
    ];

    protected $guarded = [];

        
}