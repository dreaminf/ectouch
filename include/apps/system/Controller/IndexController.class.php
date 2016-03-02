<?php
namespace system\Controller;

use Think\Controller;

class IndexController extends Controller
{
    public function index()
    {
        $this->show('testaa','utf-8');
    }
}