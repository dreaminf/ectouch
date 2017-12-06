<?php

namespace App\Api\Models\V1;

use app\models\BaseModel;

class LinkGoods extends BaseModel
{
    protected $connection = 'shop';

    protected $table      = 'link_goods';

    public    $timestamps = false;

    protected $visible = [];

    // protected $appends = ['goods_id', 'name'];

    protected $guarded = [];


    // public function getPromoAttribute()
    // {
    //     return $this->act_desc;
    // }

    // public function getNameAttribute()
    // {
    //     return $this->act_name;
    // }
    
    public static function getLinkGoodIds($id)
    {	
        if($model = self::where('goods_id', $id)->get(['link_goods_id']))
        {
        	return $model;
        }
        return [0];
       
    }
}