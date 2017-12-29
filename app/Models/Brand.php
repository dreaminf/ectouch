<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Brand
 */
class Brand extends Model
{
    protected $table = 'brand';

    protected $primaryKey = 'brand_id';

    public $timestamps = false;

    protected $fillable = [
        'brand_name',
        'brand_logo',
        'brand_desc',
        'site_url',
        'sort_order',
        'is_show'
    ];

    protected $guarded = [];

        
}