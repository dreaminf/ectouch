<?php

namespace App\Api\Controllers\V2;

use App\Api\Controllers\Controller;
use App\Api\Models\V2\Keywords;

class SearchController extends Controller
{
    //POST  ecapi.search.keyword.list
    public function index()
    {
        return $this->json(Keywords::getHot());
    }
}