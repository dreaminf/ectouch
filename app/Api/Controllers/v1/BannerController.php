<?php

namespace App\Api\Controllers\v1;

use App\Api\Models\V1\Banner;

class BannerController extends BaseController
{

    /**
     * POST ecapi.banner.list
     */
    public function actionIndex()
    {
        $model = Banner::getList();

        return $this->json($model);
    }
}
