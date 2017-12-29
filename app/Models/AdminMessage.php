<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AdminMessage
 */
class AdminMessage extends Model
{
    protected $table = 'admin_message';

    protected $primaryKey = 'message_id';

    public $timestamps = false;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'sent_time',
        'read_time',
        'readed',
        'deleted',
        'title',
        'message'
    ];

    protected $guarded = [];

        
}