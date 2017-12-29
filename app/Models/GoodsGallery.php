<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class GoodsGallery
 */
class GoodsGallery extends Model
{
    protected $table = 'goods_gallery';

    protected $primaryKey = 'img_id';

    public $timestamps = false;

    protected $fillable = [
        'goods_id',
        'img_url',
        'img_desc',
        'thumb_url',
        'img_original'
    ];

    protected $guarded = [];

        
}