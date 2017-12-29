<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class RegExtendInfo
 */
class RegExtendInfo extends Model
{
    protected $table = 'reg_extend_info';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'reg_field_id',
        'content'
    ];

    protected $guarded = [];

        
}