<?php

namespace App\Api\Models\V2;

use App\Api\Models\BaseModel;

class GoodsActivity extends BaseModel
{

    protected $table = 'goods_activity';

    public $timestamps = false;

    protected $visible = ['promo', 'name'];

    protected $appends = ['promo', 'name'];

    protected $guarded = [];

}