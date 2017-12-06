<?php

namespace App\Api\Controllers\v2;

use Yii;
use yii\rest\Controller;

class ArticleController extends Controller
{

    /**
     * @return array
     */
    public function actionIndex()
    {
        return [
            'code' => 200,
            'message' => 'article.v2.api.ok'
        ];
    }

    public function actionGet($id = 0)
    {
        return $id;
    }
}
