<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class EmailList
 */
class EmailList extends Model
{
    protected $table = 'email_list';

    public $timestamps = false;

    protected $fillable = [
        'email',
        'stat',
        'hash'
    ];

    protected $guarded = [];

        
}