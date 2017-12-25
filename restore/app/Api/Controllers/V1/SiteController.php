<?php

namespace app\api\controllers\v1;

use app\api\models\v1\Goods;
use app\api\models\v1\ShopConfig;
use app\api\models\v1\Ad;

class SiteController extends BaseController
{
    //POST  ecapi.site.get
    public function actionIndex()
    {

        $rules = [
            [['page', 'per_page'], 'required'],
            [['page', 'per_page'], 'integer', 'min'=>1]
        ];

        if ($error = $this->validateInput($rules)) {
            return $error;
        }
        $id = 265;
        $goodsList = Goods::getBestGoodsList($this->validated);
        $banner = Ad::getBanner($id);
        return $this->json(['banner'=>$banner, 'goodsList'=>$goodsList]);
    }
}