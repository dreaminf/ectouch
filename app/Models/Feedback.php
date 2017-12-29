<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Feedback
 */
class Feedback extends Model
{
    protected $table = 'feedback';

    protected $primaryKey = 'msg_id';

    public $timestamps = false;

    protected $fillable = [
        'parent_id',
        'user_id',
        'user_name',
        'user_email',
        'msg_title',
        'msg_type',
        'msg_status',
        'msg_content',
        'msg_time',
        'message_img',
        'order_id',
        'msg_area'
    ];

    protected $guarded = [];

        
}