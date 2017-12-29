<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class GoodsCat
 */
class GoodsCat extends Model
{
    protected $table = 'goods_cat';

    public $timestamps = false;

    protected $fillable = [
        'goods_id',
        'cat_id'
    ];

    protected $guarded = [];

        
}