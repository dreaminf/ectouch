<?php

namespace App\Api\Models\V2;

use App\Api\Models\BaseModel;

class Category extends BaseModel
{
    protected $table = 'category';

    public $timestamps = false;

    protected $guarded = [];

}
