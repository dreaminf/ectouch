<?php
namespace site\Controller;

use Think\Controller;

class IndexController extends Controller
{
    public function index()
    {
        $this->show('s','utf-8');
    }
}