<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Action extends IndexController {
    
    public function region(){
        $type   = I('type', 0, 'intval');
        $parent = I('parent', 0, 'intval');

        $arr['regions'] = get_regions($type, $parent);
        $arr['type']    = $type;
        $arr['target']  = I('target', '');

        $json = new JSON;
        echo $json->encode($arr);
    }

    public function captcha(){
        $params = array(
            'fontSize' => 14,    // 验证码字体大小
            'length' => 4,     // 验证码位数
            'useNoise' => false, // 关闭验证码杂点
            'fontttf' => '4.ttf'
        );
        $this->load->library('verify', $params);
        $this->verify->entry();
    }

}
