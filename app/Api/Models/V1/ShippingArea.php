<?php

namespace App\Api\Models\V1;

use app\models\BaseModel;

class ShippingArea extends BaseModel
{
    protected $connection = 'shop';

    protected $table      = 'shipping_area';

    public    $timestamps = false;
    
}