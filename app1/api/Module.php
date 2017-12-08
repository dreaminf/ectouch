<?php

namespace app\api;

use Yii;
use yii\web\Response;

/**
 * api module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\api\controllers';

    /**
     * @inheritdoc
     */
    public $defaultRoute = 'api';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // initialize the module with the configuration loaded from config.php
        Yii::$app->response->format = Response::FORMAT_JSON;
    }
}
