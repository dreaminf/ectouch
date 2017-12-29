<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CollectGoods
 */
class CollectGoods extends Model
{
    protected $table = 'collect_goods';

    protected $primaryKey = 'rec_id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'goods_id',
        'add_time',
        'is_attention'
    ];

    protected $guarded = [];

        
}