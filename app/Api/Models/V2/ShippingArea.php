<?php

namespace App\Models\V2;

use App\Models\BaseModel;

class ShippingArea extends BaseModel
{
    protected $connection = 'shop';

    protected $table      = 'shipping_area';

    public    $timestamps = false;
    
}