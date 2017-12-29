<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Stats
 */
class Stats extends Model
{
    protected $table = 'stats';

    public $timestamps = false;

    protected $fillable = [
        'access_time',
        'ip_address',
        'visit_times',
        'browser',
        'system',
        'language',
        'area',
        'referer_domain',
        'referer_path',
        'access_url'
    ];

    protected $guarded = [];

        
}