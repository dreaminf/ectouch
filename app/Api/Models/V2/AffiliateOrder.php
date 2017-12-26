<?php

namespace App\Api\Models\V2;

use App\Api\Models\BaseModel;
use DB;

class AffiliateOrder extends BaseModel
{

    protected $table = 'order_info';

    protected $primaryKey = 'order_id';

    public $timestamps = false;

    protected $guarded = [];

}
