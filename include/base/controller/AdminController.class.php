<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：AdminController.class.php
 * ----------------------------------------------------------------------------
 * 功能描述：ECTOUCH 后台控制器
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */

/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

class AdminController extends BaseController {
    protected $sess = null;

    public function __construct() {
        parent::__construct();

        require BASE_PATH .'classes/session.php';
        $this->sess = new session(self::$db, self::$ecs->table('sessions'), self::$ecs->table('sessions_data'), 'ECSCP_ID');

        $this->checkLogin();
        $this->assign('lang', L());
    }

    protected function checkLogin() {
        //不需要登录验证的页面
        $access = array('Wechat', 'Extend', 'Authorization', 'Navigator', 'Index' => array('license', 'uploader'));
        
        //如果当前访问是无需登录验证，则直接返回
        if (!in_array(CONTROLLER_NAME, $access) && !in_array(ACTION_NAME, $access[CONTROLLER_NAME])) {
            $this->redirect('./admin');
        }

        if (intval($_SESSION['admin_id']) > 0) {
            return true;
        }

        //没有登录,则跳转到登录页面
        if (!$this->isLogin()) {
             $this->redirect(url('admin/index/login'));
        }
        return true;
    }

    //判断是否登录
    protected function isLogin() {
        if (empty($_SESSION[APP_NAME . '_USERINFO'])) {
            /* session 不存在，检查cookie */
            if (!empty($_COOKIE['ECTOUCHCP']['ADMIN_ID']) && !empty($_COOKIE['ECTOUCHCP']['ADMIN_PWD'])) {
                // 找到了cookie, 验证cookie信息
                $condition['user_id'] = intval($_COOKIE['ECTOUCHCP']['ADMIN_ID']);
                $userInfo = $this->model->table('admin_user')->field('user_id, user_name, password, email, last_login, ec_salt')->where($condition)->find();
                if (empty($userInfo)) {
                    // 没有找到这个记录
                    setcookie($_COOKIE['ECTOUCHCP']['ADMIN_ID'], '', 1);
                    setcookie($_COOKIE['ECTOUCHCP']['ADMIN_PWD'], '', 1);
                    return false;
                } else {
                    // 检查密码是否正确
                    if (md5(md5($userInfo['user_id'] . $userInfo['user_name']) . C('hash_code')) == $_COOKIE['ECTOUCHCP']['ADMIN_PWD']) {
                        $this->setLogin($userInfo);
                        $data['last_login'] = gmtime();
                        $data['last_ip'] = get_client_ip();
                        $this->model->table('admin_user')->data($data)->where($condition)->update();
                        $this->userInfo = $_SESSION[APP_NAME . '_USERINFO'];
                        return true;
                    } else {
                        setcookie($_COOKIE['ECTOUCHCP']['ADMIN_ID'], '', 1);
                        setcookie($_COOKIE['ECTOUCHCP']['ADMIN_PWD'], '', 1);
                        return false;
                    }
                }
            }
            return false;
        } else {
            $this->userInfo = $_SESSION[APP_NAME . '_USERINFO'];
            return true;
        }
    }

    //设置登录
    protected function setLogin($userInfo) {
        $_SESSION[APP_NAME . '_USERINFO'] = $userInfo;
    }

    //退出登录
    protected function clearLogin($url = '') {
        //清除来路
        unset($_SESSION['safe_route']);
        /* 清除cookie */
        setcookie('ECTOUCHCP[ADMIN_ID]', '', 1);
        setcookie('ECTOUCHCP[ADMIN_PWD]', '', 1);
        $_SESSION[APP_NAME . '_USERINFO'] = NULL;
        if (!empty($url)) {
            $_SESSION['safe_route'] = true;
            $this->redirect($url);
        }
        return true;
    }

    //$upload_dir上传的目录名
    protected function ectouchUpload($key = '', $upload_dir = 'images', $thumb = false, $width = 220, $height = 220) {
        $upload = new UploadFile();
        //设置上传文件大小
        $upload->maxSize = 1024 * 1024 * 5; //最大10M,但最佳5M以内。
        //设置上传文件类型
        $upload->allowExts = explode(',', 'jpg,jpeg,gif,png,bmp,mp3,amr,mp4');
        //生成缩略图
        $upload->thumb = $thumb;
        //缩略图大小
        $upload->thumbMaxWidth = $width;
        $upload->thumbMaxHeight = $height;

        //设置附件上传目录
        $upload->savePath = './data/attached/' . $upload_dir . "/";

        if (!$upload->upload($key)) {
            //捕获上传异常
            return array('error' => 1, 'message' => $upload->getErrorMsg());
        } else {
            //取得成功上传的文件信息
            return array('error' => 0, 'message' => $upload->getUploadFileInfo());
        }
    }

}

class_alias('AdminController', 'ECTouch');
