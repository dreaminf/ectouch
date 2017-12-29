<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Keywords
 */
class Keywords extends Model
{
    protected $table = 'keywords';

    public $timestamps = false;

    protected $fillable = [
        'date',
        'searchengine',
        'keyword',
        'count'
    ];

    protected $guarded = [];

        
}