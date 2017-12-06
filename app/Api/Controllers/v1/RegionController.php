<?php

namespace App\Api\Controllers\v1;

use App\Api\Models\V1\Region;

class RegionController extends BaseController
{

    private $regions;

    public function behaviors()
    {
        return [
            [
                'class' => 'yii\filters\HttpCache',
                'only' => ['index'],
                'etagSeed' => function ($action, $params) {
                    $this->regions = Region::getList();
                    return md5(serialize($this->regions));
                },
            ],
        ];
    }

    public function actionIndex()
    {
        $this->regions = Region::getList();
        return $this->json($this->regions);
    }
}
