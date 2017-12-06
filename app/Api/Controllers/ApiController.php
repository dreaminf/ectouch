<?php

namespace App\Api\Controllers;

use Yii;
use yii\web\Controller;

class ApiController extends Controller
{

    public $layout = false;

    /**
     * @return array
     */
    public function actionIndex()
    {
        return [
            'code' => 200,
            'message' => 'ectouch.api.ok'
        ];
    }
}
