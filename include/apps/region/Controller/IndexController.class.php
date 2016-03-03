<?php
namespace region\Controller;

use Think\Controller;

class IndexController extends Controller
{
    public function indexAction()
    {
        $type = I('type', 0);
        $parent = I('parent', 0);

        $arr['regions'] = get_regions($type, $parent);
        $arr['type'] = $type;
        $arr['target'] = I('target', '');
        exit(json_encode($arr));
    }
}