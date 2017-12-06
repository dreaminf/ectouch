<?php

namespace App\Custom\Controller;

use App\Http\Controllers\IndexController as BaseController;

class IndexController extends BaseController
{
    public function index()
    {
        return 'Hello Developer.';
    }
}
