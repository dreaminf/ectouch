<?php

namespace App\Api\Models\V1;

use app\models\BaseModel;

class AreaRegion extends BaseModel
{
    protected $connection = 'shop';

    protected $table      = 'area_region';

    public    $timestamps = false;

    protected $visible = [];

}