<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ErrorLog
 */
class ErrorLog extends Model
{
    protected $table = 'error_log';

    public $timestamps = false;

    protected $fillable = [
        'info',
        'file',
        'time'
    ];

    protected $guarded = [];

        
}