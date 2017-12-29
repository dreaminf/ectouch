<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Attribute
 */
class Attribute extends Model
{
    protected $table = 'attribute';

    protected $primaryKey = 'attr_id';

    public $timestamps = false;

    protected $fillable = [
        'cat_id',
        'attr_name',
        'attr_input_type',
        'attr_type',
        'attr_values',
        'attr_index',
        'sort_order',
        'is_linked',
        'attr_group'
    ];

    protected $guarded = [];

        
}