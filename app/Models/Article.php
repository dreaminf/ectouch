<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Article
 */
class Article extends Model
{
    protected $table = 'article';

    protected $primaryKey = 'article_id';

    public $timestamps = false;

    protected $fillable = [
        'cat_id',
        'title',
        'content',
        'author',
        'author_email',
        'keywords',
        'article_type',
        'is_open',
        'add_time',
        'file_url',
        'open_type',
        'link',
        'description'
    ];

    protected $guarded = [];

        
}