<?php

namespace App\Api\Models\V2;

use App\Api\Models\BaseModel;

class MemberPrice extends BaseModel
{

    protected $table = 'member_price';

    public $timestamps = false;

    public static function getMemberPriceByUid($rank, $goods_id)
    {
        return self::where('user_rank', $rank)->where('goods_id', $goods_id)->value('user_price');
    }

}
