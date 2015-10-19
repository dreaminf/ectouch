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

$payment_lang = ROOT_PATH . 'plugins/connect/language/' . C('lang') . '/' . basename(__FILE__);

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
    $modules[$i]['type'] = 'wechat';

    $modules[$i]['className'] = 'wechat';
    // 作者信息
    $modules[$i]['author'] = 'ECTouch Team';

    // 作者QQ
    $modules[$i]['qq'] = '10000';

    // 作者邮箱
    $modules[$i]['email'] = 'support@ectouch.cn';

    // 申请网址
    $modules[$i]['website'] = 'http://mp.wexin.qq.com';

    // 版本号
    $modules[$i]['version'] = '1.0';

    // 更新日期
    $modules[$i]['date'] = '2014-8-19';
    /* 配置信息 */
    $modules[$i]['config'] = array(
        array('type' => 'text', 'name' => 'token', 'value' => ''),
        array('type' => 'text', 'name' => 'app_id', 'value' => ''),
        array('type' => 'text', 'name' => 'app_secret', 'value' => ''),
        array('type' => 'text', 'name' => 'redirecturi', 'value' => ''),
    );
    return;
}

/**
 * WECHAT API client
 */
class wechat {

    private $token = '';
    private $appid = '';
    private $appkey = '';
    private $redirecturi = '';

    /**
     * 构造函数
     *
     * @param unknown $app            
     * @param string $access_token            
     */
    public function __construct($conf, $access_token = NULL) {
        $this->token = $conf['token'];
        $this->appid = $conf['app_id'];
        $this->appkey = $conf['app_key'];
        $this->redirecturi = $conf['redirecturi'];
    }

    /**
     * 微信OAuth操作
     */
    static function oauth()
    {
        $config['token'] = $this->token;
        $config['appid'] = $this->appid;
        $config['appsecret'] = $this->appsecret;
        if(file_exists(ROOT_PATH . 'inlcude/vendor/Wechat.class.php')){
            require_once(ROOT_PATH . 'inlcude/vendor/Wechat.class.php');
            $weObj = new Wechat($config);
        }
        if(!isset($weObj)){
            header('Location:' . __HOST__, true, 302);
        }
        // 微信浏览器浏览
        if (is_wechat_browser() && ($_SESSION['user_id'] === 0 || empty($_SESSION['openid']))) {
            if (! isset($_SESSION['redirect_url'])) {
                session('redirect_url', __HOST__ . $_SERVER['REQUEST_URI']);
            }
            $url = $weObj->getOauthRedirect($this->redirecturi, 1);
            if (isset($_GET['code']) && !empty($_GET['code'])) {
                $token = $weObj->getOauthAccessToken();
                if ($token) {
                    $userinfo = $weObj->getOauthUserinfo($token['access_token'], $token['openid']);
                    self::update_weixin_user($userinfo, $weObj);
                    if (! empty($_SESSION['redirect_url'])) {
                        $redirect_url = session('redirect_url');
                        header('Location:' . $redirect_url, true, 302);
                        exit();
                    }
                } else {
                    header('Location:' . $url, true, 302);
                    exit();
                }
            } else {
                header('Location:' . $url, true, 302);
                exit();
            }
        }
    }

    /**
     * 更新微信用户信息
     *
     * @param unknown $userinfo          
     * @param unknown $weObj            
     */
    static function update_weixin_user($userinfo, $weObj)
    {
        $time = time();
        $ret = model('Base')->model->table('wechat_user')->field('openid, ect_uid')->where('openid = "' . $userinfo['openid'] . '"')->find();
        if (empty($ret)) {
            //微信用户绑定会员id
            $ect_uid = 0;
            //查看公众号是否绑定
            if($userinfo['unionid']){
                $ect_uid = model('Base')->model->table('wechat_user')->field('ect_uid')->where(array('unionid'=>$userinfo['unionid']))->getOne();
            }

            //未绑定
            if(empty($ect_uid)){
                $username = 'wx_' . time().mt_rand(1, 99);
                $password = 'ecmoban';
                // 通知模版
                $template = '默认用户名：' . $username . "\r\n" . '默认密码：' . $password;
                // 会员注册
                $domain = get_top_domain();
                if (model('Users')->register($username, $password, $username . '@' . $domain) !== false) {
                    $data['user_rank'] = 99;
                    
                    model('Base')->model->table('users')->data($data)->where('user_name = "' . $username . '"')->update();
                    model('Users')->update_user_info();
                } else {
                    die('授权失败，如重试一次还未解决问题请联系管理员');
                }
                $data1['ect_uid'] = $_SESSION['user_id'];
            }
            else{
                //已绑定
                $username = model('Base')->model->table('users')->field('user_name')->where(array('user_id'=>$ect_uid))->getOne();
                $template = '您已拥有帐号，用户名为'.$username;
                $data1['ect_uid'] = $ect_uid;
            }
            
            // 获取用户所在分组ID
            $group_id = $weObj->getUserGroup($userinfo['openid']);
            $group_id = $group_id ? $group_id : 0;

            $data1['wechat_id'] = model('Base')->model->table('wechat')->field('id')->where(array('type'=>2, 'status'=>1))->getOne();
            $data1['subscribe'] = 1;
            $data1['openid'] = $userinfo['openid'];
            $data1['nickname'] = $userinfo['nickname'];
            $data1['sex'] = $userinfo['sex'];
            $data1['city'] = $userinfo['city'];
            $data1['country'] = $userinfo['country'];
            $data1['province'] = $userinfo['province'];
            $data1['language'] = $userinfo['country'];
            $data1['headimgurl'] = $userinfo['headimgurl'];
            $data1['subscribe_time'] = $time;
            $data1['group_id'] = $group_id;
            $data1['unionid'] = $userinfo['unionid'];
            
            model('Base')->model->table('wechat_user')->data($data1)->insert();
        } else {
            //开放平台有privilege字段,公众平台没有
            unset($userinfo['privilege']);
            $userinfo['subscribe'] = 1;
            model('Base')->model->table('wechat_user')->data($userinfo)->where(array('openid'=> $userinfo['openid']))->update();
            $new_user_name = model('Base')->model->table('users')->field('user_name')->where(array('user_id'=>$ret['ect_uid']))->getOne();
            ECTouch::user()->set_session($new_user_name);
            ECTouch::user()->set_cookie($new_user_name);
            model('Users')->update_user_info();
        }
        session('openid', $userinfo['openid']);
    }

}
