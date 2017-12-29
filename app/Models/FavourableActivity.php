<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class FavourableActivity
 */
class FavourableActivity extends Model
{
    protected $table = 'favourable_activity';

    protected $primaryKey = 'act_id';

    public $timestamps = false;

    protected $fillable = [
        'act_name',
        'start_time',
        'end_time',
        'user_rank',
        'act_range',
        'act_range_ext',
        'min_amount',
        'max_amount',
        'act_type',
        'act_type_ext',
        'gift',
        'sort_order'
    ];

    protected $guarded = [];

        
}