<?php

namespace App\Api\Models\V1;
use app\models\BaseModel;

class Sns extends BaseModel {

    protected $connection = 'shop';
    protected $table      = 'sns';
    protected $primaryKey = 'user_id';
    public    $timestamps = true;
}
