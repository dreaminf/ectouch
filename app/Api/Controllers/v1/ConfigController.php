<?php

namespace App\Api\Controllers\v1;

use App\Api\Models\V1\Configs;

class ConfigController extends BaseController
{

    public function actionIndex()
    {
        $data = Configs::getList();
        return $this->json($data);
    }

}
