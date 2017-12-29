<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class VolumePrice
 */
class VolumePrice extends Model
{
    protected $table = 'volume_price';

    public $timestamps = false;

    protected $fillable = [
        'price_type',
        'goods_id',
        'volume_number',
        'volume_price'
    ];

    protected $guarded = [];

        
}