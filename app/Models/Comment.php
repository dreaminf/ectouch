<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Comment
 */
class Comment extends Model
{
    protected $table = 'comment';

    protected $primaryKey = 'comment_id';

    public $timestamps = false;

    protected $fillable = [
        'comment_type',
        'id_value',
        'email',
        'user_name',
        'content',
        'comment_rank',
        'add_time',
        'ip_address',
        'status',
        'parent_id',
        'user_id'
    ];

    protected $guarded = [];

        
}