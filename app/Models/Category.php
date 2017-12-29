<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Category
 */
class Category extends Model
{
    protected $table = 'category';

    protected $primaryKey = 'cat_id';

    public $timestamps = false;

    protected $fillable = [
        'cat_name',
        'keywords',
        'cat_desc',
        'parent_id',
        'sort_order',
        'template_file',
        'measure_unit',
        'show_in_nav',
        'style',
        'is_show',
        'grade',
        'filter_attr'
    ];

    protected $guarded = [];

        
}