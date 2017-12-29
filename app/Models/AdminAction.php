<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AdminAction
 */
class AdminAction extends Model
{
    protected $table = 'admin_action';

    protected $primaryKey = 'action_id';

    public $timestamps = false;

    protected $fillable = [
        'parent_id',
        'action_code',
        'relevance'
    ];

    protected $guarded = [];

        
}