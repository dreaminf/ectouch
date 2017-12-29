<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ShippingArea
 */
class ShippingArea extends Model
{
    protected $table = 'shipping_area';

    protected $primaryKey = 'shipping_area_id';

    public $timestamps = false;

    protected $fillable = [
        'shipping_area_name',
        'shipping_id',
        'configure'
    ];

    protected $guarded = [];

        
}