<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ExchangeGoods
 */
class ExchangeGoods extends Model
{
    protected $table = 'exchange_goods';

    protected $primaryKey = 'goods_id';

    public $timestamps = false;

    protected $fillable = [
        'exchange_integral',
        'is_exchange',
        'is_hot'
    ];

    protected $guarded = [];

        
}