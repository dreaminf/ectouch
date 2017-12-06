<?php

namespace App\Api\Models\V1;
use app\models\BaseModel;

class Cert extends BaseModel {

    protected $connection = 'shop';
    protected $table      = 'cert';
    public    $timestamps = true;
}
