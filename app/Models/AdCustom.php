<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AdCustom
 */
class AdCustom extends Model
{
    protected $table = 'ad_custom';

    protected $primaryKey = 'ad_id';

    public $timestamps = false;

    protected $fillable = [
        'ad_type',
        'ad_name',
        'add_time',
        'content',
        'url',
        'ad_status'
    ];

    protected $guarded = [];

        
}