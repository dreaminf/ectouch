<?php

namespace App\Api\Models\V2;

use App\Api\Models\BaseModel;

class Sns extends BaseModel
{

    protected $table = 'sns';

    protected $primaryKey = 'user_id';

    public $timestamps = true;
}
