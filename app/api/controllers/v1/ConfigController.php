<?php

namespace app\api\controllers\v1;

use app\api\models\v1\Configs;

class ConfigController extends BaseController
{

    public function actionIndex()
    {
        $data = Configs::getList();
        return $this->json($data);
    }

}
