<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DeliveryOrder
 */
class DeliveryOrder extends Model
{
    protected $table = 'delivery_order';

    protected $primaryKey = 'delivery_id';

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
        'agency_id'
    ];

    protected $guarded = [];

        
}