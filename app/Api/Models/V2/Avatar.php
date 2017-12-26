<?php

namespace App\Api\Models\V2;

use App\Api\Models\BaseModel;

class Avatar extends BaseModel
{
    protected $table = 'avatar';

    public $timestamps = false;

    protected $guarded = [];
}
