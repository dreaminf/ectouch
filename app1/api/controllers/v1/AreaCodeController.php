<?php

namespace app\api\controllers\v1;

use app\api\models\v1\AreaCode;

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
