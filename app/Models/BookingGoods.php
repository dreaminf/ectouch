<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BookingGoods
 */
class BookingGoods extends Model
{
    protected $table = 'booking_goods';

    protected $primaryKey = 'rec_id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'email',
        'link_man',
        'tel',
        'goods_id',
        'goods_desc',
        'goods_number',
        'booking_time',
        'is_dispose',
        'dispose_user',
        'dispose_time',
        'dispose_note'
    ];

    protected $guarded = [];

        
}