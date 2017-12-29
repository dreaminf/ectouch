<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Topic
 */
class Topic extends Model
{
    protected $table = 'topic';

    public $timestamps = false;

    protected $fillable = [
        'topic_id',
        'title',
        'intro',
        'start_time',
        'end_time',
        'data',
        'template',
        'css',
        'topic_img',
        'title_pic',
        'base_style',
        'htmls',
        'keywords',
        'description'
    ];

    protected $guarded = [];

        
}