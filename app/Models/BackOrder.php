<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BackOrder
 */
class BackOrder extends Model
{
    protected $table = 'back_order';

    protected $primaryKey = 'back_id';

    public $timestamps = false;

    protected $fillable = [
        'delivery_sn',
        'order_sn',
        'order_id',
        'invoice_no',
        'add_time',
        'shipping_id',
        'shipping_name',
        'user_id',
        'action_user',
        'consignee',
        'address',
        'country',
        'province',
        'city',
        'district',
        'sign_building',
        'email',
        'zipcode',
        'tel',
        'mobile',
        'best_time',
        'postscript',
        'how_oos',
        'insure_fee',
        'shipping_fee',
        'update_time',
        'suppliers_id',
        'status',
        'return_time',
        'agency_id'
    ];

    protected $guarded = [];

        
}