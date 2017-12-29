<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AdPosition
 */
class AdPosition extends Model
{
    protected $table = 'ad_position';

    protected $primaryKey = 'position_id';

    public $timestamps = false;

    protected $fillable = [
        'position_name',
        'ad_width',
        'ad_height',
        'position_desc',
        'position_style'
    ];

    protected $guarded = [];

        
}