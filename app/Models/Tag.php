<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Tag
 */
class Tag extends Model
{
    protected $table = 'tag';

    protected $primaryKey = 'tag_id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'goods_id',
        'tag_words'
    ];

    protected $guarded = [];

        
}