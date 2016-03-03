<?php
namespace captcha\Controller;
use common\Controller\FrontendController;
use Think\Verify;
class IndexController extends FrontendController {
    /**
     * 验证码
     */
    public function indexAction()
    {
        $params = array(
            'fontSize' => 14, // 验证码字体大小
            'length' => 4, // 验证码位数
            'useNoise' => false, // 关闭验证码杂点
            'fontttf' => '4.ttf'
        );
        $verify = new Verify($params);
        $verify->entry();
    }
}