<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Users
 */
class Users extends Model
{
    protected $table = 'users';

    protected $primaryKey = 'user_id';

    public $timestamps = false;

    protected $fillable = [
        'email',
        'user_name',
        'password',
        'question',
        'answer',
        'sex',
        'birthday',
        'user_money',
        'frozen_money',
        'pay_points',
        'rank_points',
        'address_id',
        'reg_time',
        'last_login',
        'last_time',
        'last_ip',
        'visit_count',
        'user_rank',
        'is_special',
        'ec_salt',
        'salt',
        'parent_id',
        'flag',
        'alias',
        'msn',
        'qq',
        'office_phone',
        'home_phone',
        'mobile_phone',
        'is_validated',
        'credit_line',
        'passwd_question',
        'passwd_answer'
    ];

    protected $guarded = [];

        
}