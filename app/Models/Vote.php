<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Vote
 */
class Vote extends Model
{
    protected $table = 'vote';

    protected $primaryKey = 'vote_id';

    public $timestamps = false;

    protected $fillable = [
        'vote_name',
        'start_time',
        'end_time',
        'can_multi',
        'vote_count'
    ];

    protected $guarded = [];

        
}