<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class VoteOption
 */
class VoteOption extends Model
{
    protected $table = 'vote_option';

    protected $primaryKey = 'option_id';

    public $timestamps = false;

    protected $fillable = [
        'vote_id',
        'option_name',
        'option_count',
        'option_order'
    ];

    protected $guarded = [];

        
}