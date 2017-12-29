<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserAddress
 */
class UserAddress extends Model
{
    protected $table = 'user_address';

    protected $primaryKey = 'address_id';

    public $timestamps = false;

    protected $fillable = [
        'address_name',
        'user_id',
        'consignee',
        'email',
        'country',
        'province',
        'city',
        'district',
        'address',
        'zipcode',
        'tel',
        'mobile',
        'sign_building',
        'best_time'
    ];

    protected $guarded = [];

        
}