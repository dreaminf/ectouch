<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class GoodsArticle
 */
class GoodsArticle extends Model
{
    protected $table = 'goods_article';

    public $timestamps = false;

    protected $fillable = [
        'goods_id',
        'article_id',
        'admin_id'
    ];

    protected $guarded = [];

        
}