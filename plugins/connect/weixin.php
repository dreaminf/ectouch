<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：wechat.php
 * ----------------------------------------------------------------------------
 * 功能描述：微信登录插件
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */

/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

$payment_lang = ROOT_PATH . 'plugins/connect/languages/' . C('lang') . '/' . basename(__FILE__);

if (file_exists($payment_lang)) {
    include_once ($payment_lang);
    L($_LANG);
}
/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE) {
    $i = isset($modules) ? count($modules) : 0;
    /* 类名 */
    $modules[$i]['name'] = '微信登录插件';
    // 文件名，不包含后缀
    $modules[$i]['type'] = 'weixin';

    $modules[$i]['className'] = 'weixin';
    // 作者信息
    $modules[$i]['author'] = 'ECTouch Team';

    // 作者QQ
    $modules[$i]['qq'] = '10000';

    // 作者邮箱
    $modules[$i]['email'] = 'support@ectouch.cn';

    // 申请网址
    $modules[$i]['website'] = 'http://mp.weixin.qq.com';

    // 版本号
    $modules[$i]['version'] = '1.0';

    // 更新日期
    $modules[$i]['date'] = '2014-8-19';
    /* 配置信息 */
    $modules[$i]['config'] = array(
        array('type' => 'text', 'name' => 'app_id', 'value' => ''),
        array('type' => 'text', 'name' => 'app_secret', 'value' => ''),
        array('type' => 'text', 'name' => 'token', 'value' => ''),
    );
    return;
}

/**
 * WECHAT API client
 */
class weixin {

    private $token = '';
    private $appid = '';
    private $appkey = '';
    private $weObj = '';

    /**
     * 构造函数
     *
     * @param unknown $app
     * @param string $access_token
     */
    public function __construct($conf) {
        $this->token = $conf['token'];
        $this->appid = $conf['app_id'];
        $this->appsecret = $conf['app_secret'];

        $config['token'] = $this->token;
        $config['appid'] = $this->appid;
        $config['appsecret'] = $this->appsecret;

        $this->weObj = new Wechat($config);
    }

    /**
     * 获取授权地址
     */
    public function act_login($callback_url, $state = 'wechat_oauth', $snsapi = 'snsapi_userinfo'){
        // 微信浏览器浏览
        if (is_wechat_browser() && ($_SESSION['user_id'] === 0 || empty($_SESSION['openid']))) {
            return $this->weObj->getOauthRedirect($callback_url, $state, $snsapi);
        }else{
            show_message("请在微信内访问或者已经登录。", L('relogin_lnk'), url('login', array('referer' => urlencode($callback_url))), 'error');
        }
    }

    /**
     * 回调用户数据
     */
    public function call_back($callback_url, $code)
    {
        if (!empty($code)) {
            $token = $this->weObj->getOauthAccessToken();
            $userinfo = $this->weObj->getOauthUserinfo($token['access_token'], $token['openid']);

            if (!empty($userinfo) && !empty($userinfo['unionid'])) {
                $_SESSION['wechat_user'] = $userinfo;  // 兼容

                $_SESSION['openid'] = $userinfo['openid'];
                $_SESSION['nickname'] = $userinfo['nickname'];
                $_SESSION['avatar'] = $userinfo['headimgurl'];
                setcookie('openid', $userinfo['openid'], gmtime() + 86400 * 7);

                $data = array(
                    'openid' => $userinfo['unionid'],
                    'name' => $userinfo['nickname'],
                    'sex' => $userinfo['sex'],
                    'avatar' => $userinfo['headimgurl'],
                    'access_token' => $token['access_token'],
                    'expires_in' => $token['expires_in'],
                );

                if(is_wechat_browser()){
                    //公众号信息
                    $wechat = model('Base')->model->table('wechat')->field('id, oauth_status')->where(array('type' => 2, 'status' => 1, 'default_wx' => 1))->find();
                    $wechat['id'] = !empty($wechat['id']) ? $wechat['id'] : 1;
                    $this->update_weixin_user($userinfo, $wechat['id']);
                }
                return $data;
            } else {
                // echo '获取授权信息失败';
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 更新微信用户信息
     *
     * @param unknown $userinfo
     * @param unknown $weObj
     */
    public function update_weixin_user($res, $wechat_id)
    {
        if(empty($res['unionid'])){
            die('授权失败，请联系管理员开通微信开放平台');
        }

        // 移除用户特权数据
        $res['privilege'] = serialize($res['privilege']);
        unset($res['privilege']);

        // 已关注用户基本信息
        // $condition = array('openid' => $data['openid'], 'wechat_id' => $wechat_id);
        // $result = model('Base')->model->table('wechat_user')->field('ect_uid, unionid')->where($condition)->find();

        $sql = "SELECT ect_uid, openid, unionid FROM {pre}wechat_user WHERE openid = '".$res['openid']."' OR unionid = '" . $res['unionid']."'  AND wechat_id = '".$wechat_id."' ";
        $userinfo = model('Base')->model->query($sql);
        $userinfo = $userinfo[0];

        if (empty($userinfo)) {
            $connect_user = model('Base')->model->table('connect_user')->where(array('open_id' => $res['unionid']))->find();
            $res['ect_uid'] = isset($connect_user['user_id']) ? $connect_user['user_id'] : 0;
            $res['wechat_id'] = $wechat_id;
            model('Base')->model->table('wechat_user')->data($res)->insert();
        } else {
            if ($userinfo['unionid']) {
                $condition = array('unionid' => $res['unionid'], 'wechat_id' => $wechat_id);
            } else {
                $condition = array('openid' => $res['openid'], 'wechat_id' => $wechat_id);
            }
            model('Base')->model->table('wechat_user')->data($res)->where($condition)->update();
        }
    }


}