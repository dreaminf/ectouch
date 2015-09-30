<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：CommonControoller.class.php
 * ----------------------------------------------------------------------------
 * 功能描述：公共控制器
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */

/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

class Common extends IndexController
{

    public function region(){
        $type   = !empty($_REQUEST['type'])   ? intval($_REQUEST['type'])   : 0;
        $parent = !empty($_REQUEST['parent']) ? intval($_REQUEST['parent']) : 0;

        $arr['regions'] = get_regions($type, $parent);
        $arr['type']    = $type;
        $arr['target']  = !empty($_REQUEST['target']) ? stripslashes(trim($_REQUEST['target'])) : '';
        $arr['target']  = htmlspecialchars($arr['target']);

        $json = new JSON;
        echo $json->encode($arr);
    }

    public function captcha(){
        $img = new captcha(ROOT_PATH . 'data/captcha/', C('captcha_width'), C('captcha_height'));
        @ob_end_clean(); //清除之前出现的多余输入
        if (isset($_REQUEST['is_login']))
        {
            $img->session_word = 'captcha_login';
        }
        $img->generate_image();
    }

}

