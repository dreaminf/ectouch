<?php

namespace app\custom\controllers;

use app\http\controllers\IndexController as BaseController;

class IndexController extends BaseController
{
    public function index()
    {
        return 'Hello Developer.';
    }
}
