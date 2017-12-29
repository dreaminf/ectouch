<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Ad
 */
class Ad extends Model
{
    protected $table = 'ad';

    protected $primaryKey = 'ad_id';

    public $timestamps = false;

    protected $fillable = [
        'position_id',
        'media_type',
        'ad_name',
        'ad_link',
        'ad_code',
        'start_time',
        'end_time',
        'link_man',
        'link_email',
        'link_phone',
        'click_count',
        'enabled'
    ];

    protected $guarded = [];

        
}