<?php

namespace App\Models\V2;

use App\Models\BaseModel;

class AreaRegion extends BaseModel
{
    protected $connection = 'shop';

    protected $table      = 'area_region';

    public    $timestamps = false;

    protected $visible = [];

}