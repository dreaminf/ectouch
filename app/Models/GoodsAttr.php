<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class GoodsAttr
 */
class GoodsAttr extends Model
{
    protected $table = 'goods_attr';

    protected $primaryKey = 'goods_attr_id';

    public $timestamps = false;

    protected $fillable = [
        'goods_id',
        'attr_id',
        'attr_value',
        'attr_price'
    ];

    protected $guarded = [];

        
}