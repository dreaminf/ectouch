<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class FriendLink
 */
class FriendLink extends Model
{
    protected $table = 'friend_link';

    protected $primaryKey = 'link_id';

    public $timestamps = false;

    protected $fillable = [
        'link_name',
        'link_url',
        'link_logo',
        'show_order'
    ];

    protected $guarded = [];

        
}