<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BackGoods
 */
class BackGoods extends Model
{
    protected $table = 'back_goods';

    protected $primaryKey = 'rec_id';

    public $timestamps = false;

    protected $fillable = [
        'back_id',
        'goods_id',
        'product_id',
        'product_sn',
        'goods_name',
        'brand_name',
        'goods_sn',
        'is_real',
        'send_number',
        'goods_attr'
    ];

    protected $guarded = [];

        
}