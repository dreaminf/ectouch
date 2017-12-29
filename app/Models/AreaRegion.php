<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AreaRegion
 */
class AreaRegion extends Model
{
    protected $table = 'area_region';

    public $timestamps = false;

    protected $fillable = [
        'shipping_area_id',
        'region_id'
    ];

    protected $guarded = [];

        
}