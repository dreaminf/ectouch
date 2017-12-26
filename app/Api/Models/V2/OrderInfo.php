<?php

namespace App\Models\V2;
use App\Models\BaseModel;

use App\Helper\Token;


class OrderInfo extends BaseModel {

    protected $connection = 'shop';
    protected $table      = 'order_info';
    public    $timestamps = false;

}
