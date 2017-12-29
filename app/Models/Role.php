<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Role
 */
class Role extends Model
{
    protected $table = 'role';

    protected $primaryKey = 'role_id';

    public $timestamps = false;

    protected $fillable = [
        'role_name',
        'action_list',
        'role_describe'
    ];

    protected $guarded = [];

        
}