<?php
namespace respond\Controller;

use Think\Controller;

class IndexController extends Controller
{
    public function indexAction()
    {
        $this->show('test','utf-8');
    }
}