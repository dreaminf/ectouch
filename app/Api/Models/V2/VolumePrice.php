<?php

namespace App\Api\Models\V2;

use App\Api\Models\BaseModel;

class VolumePrice extends BaseModel
{

    protected $table = 'volume_price';

    public $timestamps = false;

    protected $visible = ['volume_number', 'volume_price'];

    // protected $appends = ['volume_number', 'volume_price'];

    protected $guarded = [];

}