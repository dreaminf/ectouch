<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ArticleCat
 */
class ArticleCat extends Model
{
    protected $table = 'article_cat';

    protected $primaryKey = 'cat_id';

    public $timestamps = false;

    protected $fillable = [
        'cat_name',
        'cat_type',
        'keywords',
        'cat_desc',
        'sort_order',
        'show_in_nav',
        'parent_id'
    ];

    protected $guarded = [];

        
}