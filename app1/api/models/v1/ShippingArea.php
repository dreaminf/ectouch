<?php

namespace app\api\models\v1;

use app\models\BaseModel;

class ShippingArea extends BaseModel
{
    protected $connection = 'shop';

    protected $table      = 'shipping_area';

    public    $timestamps = false;
    
}