<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：GoodsControoller.class.php
 * ----------------------------------------------------------------------------
 * 功能描述：分享控件控制器
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */
/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');
class JssdkController extends CommonController
{

    public function index()
    {
        $url = addslashes($_POST['url']);
        if ($url != '') {
            // 获取公众号配置
            $wxConf = $this->getConfig();
            $this->wechat = new Wechat($wxConf);
            $sdk = $this->wechat->getJsSign($url);
			
            $data = array('status' => '200', 'data' => $sdk);
        } else {
            $data = array('status' => '100', 'message' => '缺少参数');
        }
        exit(json_encode($data));
    }

    /**
     * 获取公众号配置
     *
     * @return array
     */
    private function getConfig()
    {
        $config = $this->model->table('wechat')
            ->field('id, token, appid, appsecret')
            ->where('status = 1')
            ->find();
        if (empty($config)) {
            $config = array();
        }
        return $config;
    }
}