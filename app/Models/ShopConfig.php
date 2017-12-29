<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ShopConfig
 */
class ShopConfig extends Model
{
    protected $table = 'shop_config';

    public $timestamps = false;

    protected $fillable = [
        'parent_id',
        'code',
        'type',
        'store_range',
        'store_dir',
        'value',
        'sort_order'
    ];

    protected $guarded = [];

        
}