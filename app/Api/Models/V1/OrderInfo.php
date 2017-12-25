<?php

namespace app\api\models\v1;
use app\models\BaseModel;

use app\extensions\Token;


class OrderInfo extends BaseModel {

    protected $connection = 'shop';
    protected $table      = 'order_info';
    public    $timestamps = false;

}
