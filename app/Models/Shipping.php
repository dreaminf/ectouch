<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Shipping
 */
class Shipping extends Model
{
    protected $table = 'shipping';

    protected $primaryKey = 'shipping_id';

    public $timestamps = false;

    protected $fillable = [
        'shipping_code',
        'shipping_name',
        'shipping_desc',
        'insure',
        'support_cod',
        'enabled',
        'shipping_print',
        'print_bg',
        'config_lable',
        'print_model',
        'shipping_order'
    ];

    protected $guarded = [];

        
}