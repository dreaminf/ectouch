<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserAccount
 */
class UserAccount extends Model
{
    protected $table = 'user_account';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'admin_user',
        'amount',
        'add_time',
        'paid_time',
        'admin_note',
        'user_note',
        'process_type',
        'payment',
        'is_paid'
    ];

    protected $guarded = [];

        
}