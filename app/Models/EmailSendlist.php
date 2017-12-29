<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class EmailSendlist
 */
class EmailSendlist extends Model
{
    protected $table = 'email_sendlist';

    public $timestamps = false;

    protected $fillable = [
        'email',
        'template_id',
        'email_content',
        'error',
        'pri',
        'last_send'
    ];

    protected $guarded = [];

        
}