<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Adsense
 */
class Adsense extends Model
{
    protected $table = 'adsense';

    public $timestamps = false;

    protected $fillable = [
        'from_ad',
        'referer',
        'clicks'
    ];

    protected $guarded = [];

        
}