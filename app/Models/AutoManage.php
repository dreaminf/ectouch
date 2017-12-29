<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AutoManage
 */
class AutoManage extends Model
{
    protected $table = 'auto_manage';

    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'type',
        'starttime',
        'endtime'
    ];

    protected $guarded = [];

        
}