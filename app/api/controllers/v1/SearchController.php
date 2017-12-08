<?php

namespace app\api\controllers\v1;

use app\api\models\v1\Keywords;

class SearchController extends BaseController
{
    //POST  ecapi.search.keyword.list
    public function actionIndex()
    {
       return $this->json(Keywords::getHot());
    }
}