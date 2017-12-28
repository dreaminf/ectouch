<?php

namespace App\Api\Models\V2;

use App\Api\Models\BaseModel;


class ShopConfig extends BaseModel
{

    protected $table = 'shop_config';

    public $timestamps = true;

    public static function findByCode($code)
    {
        return self::where(['code' => $code])->value('value');
    }

    public static function getSiteInfo()
    {
        return [
            'site_info' => [
                'name' => self::findByCode('shop_name'),
                'desc' => self::findByCode('shop_desc'),
                'logo' => formatPhoto(self::findByCode('shop_logo')),
                'opening' => (bool)!self::findByCode('shop_closed'),
                'telephone' => self::findByCode('service_phone'),
                'terms_url' => env('TERMS_URL'),
                'about_url' => env('ABOUT_URL'),
            ]
        ];
    }

    private static function getConfigure($configure)
    {
        $data = [];
        $configure = unserialize($configure);
        foreach ($configure as $key => $val) {
            $data[$val['name']] = $val['value'];
        }

        return $data;
    }
}
