<?php

namespace App\Api\Models\V1;
use app\models\BaseModel;

use App\Extensions\Token;


class OrderInfo extends BaseModel {

    protected $connection = 'shop';
    protected $table      = 'order_info';
    public    $timestamps = false;

}
