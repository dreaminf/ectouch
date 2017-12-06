<?php

namespace App\Api\Controllers\v1;

use App\Api\Models\V1\AreaCode;

class AreaCodeController extends BaseController
{
    /**
     * POST ecapi.areacode.list
     */
    public function actionIndex()
    {
        $model = AreaCode::getList();

        return $this->json($model);
    }

}
