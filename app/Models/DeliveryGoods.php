<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DeliveryGoods
 */
class DeliveryGoods extends Model
{
    protected $table = 'delivery_goods';

    protected $primaryKey = 'rec_id';

    public $timestamps = false;

    protected $fillable = [
        'delivery_id',
        'goods_id',
        'product_id',
        'product_sn',
        'goods_name',
        'brand_name',
        'goods_sn',
        'is_real',
        'extension_code',
        'parent_id',
        'send_number',
        'goods_attr'
    ];

    protected $guarded = [];

        
}