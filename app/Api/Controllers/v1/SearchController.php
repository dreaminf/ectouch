<?php

namespace App\Api\Controllers\v1;

use App\Api\Models\V1\Keywords;

class SearchController extends BaseController
{
    //POST  ecapi.search.keyword.list
    public function actionIndex()
    {
       return $this->json(Keywords::getHot());
    }
}