<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：WechatControoller.class.php
 * ----------------------------------------------------------------------------
 * 功能描述：微信公众平台API
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */
/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

class WechatController extends CommonController
{

    private $weObj = '';

    private $orgid = '';

    private $wechat_id = '';

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        
        // 获取公众号配置
        $this->orgid = I('get.orgid');
        if (! empty($this->orgid)) {
            $wxinfo = $this->get_config($this->orgid);
            
            $config['token'] = $wxinfo['token'];
            $config['appid'] = $wxinfo['appid'];
            $config['appsecret'] = $wxinfo['appsecret'];
            $this->weObj = new Wechat($config);
            $this->weObj->valid();
            $this->wechat_id = $wxinfo['id'];
        }
    }

    /**
     * 执行方法
     */
    public function index()
    {
        // 事件类型
        $type = $this->weObj->getRev()->getRevType();
        $wedata = $this->weObj->getRev()->getRevData();
        //logResult(var_export($wedata, true));
        $keywords = '';
        if ($type == Wechat::MSGTYPE_TEXT) {
            $keywords = $wedata['Content'];
        } elseif ($type == Wechat::MSGTYPE_EVENT) {
            if ('subscribe' == $wedata['Event']) {
                // 用户扫描带参数二维码(未关注)
                if (isset($wedata['Ticket']) && ! empty($wedata['Ticket'])) {
                    $scene_id = $this->weObj->getRevSceneId();
                    $flag = true;
                    // 关注
                    $this->subscribe($wedata['FromUserName'], $scene_id);
                }  
                else{
                    // 关注
                    $this->subscribe($wedata['FromUserName']);
                    // 关注时回复信息
                    $this->msg_reply('subscribe');
                    exit;
                }                
            } elseif ('unsubscribe' == $wedata['Event']) {
                // 取消关注
                $this->unsubscribe($wedata['FromUserName']);
                exit();
            } elseif ('MASSSENDJOBFINISH' == $wedata['Event']) {
                // 群发结果
                $data['status'] = $wedata['Status'];
                $data['totalcount'] = $wedata['TotalCount'];
                $data['filtercount'] = $wedata['FilterCount'];
                $data['sentcount'] = $wedata['SentCount'];
                $data['errorcount'] = $wedata['ErrorCount'];
                // 更新群发结果
                $this->model->table('wechat_mass_history')
                    ->data($data)
                    ->where('msg_id = "' . $wedata['MsgID'] . '"')
                    ->update();
                exit();
            } elseif ('CLICK' == $wedata['Event']) {
                // 点击菜单
                $keywords = $wedata['EventKey'];
            } elseif ('VIEW' == $wedata['Event']) {
                $this->redirect($wedata['EventKey']);
            } elseif ('SCAN' == $wedata['Event']) {
                $scene_id = $this->weObj->getRevSceneId();
            }
        } else {
            $this->msg_reply('msg');
            exit();
        }
        //扫描二维码
        if(!empty($scene_id)){
            $qrcode_fun = $this->model->table('wechat_qrcode')->field('function')->where('scene_id = "'.$scene_id.'"')->getOne();
            //扫码引荐
            if(!empty($qrcode_fun) && isset($flag)){
                //增加扫描量
                $this->model->table('wechat_qrcode')->data('scan_num = scan_num + 1')->where('scene_id = "'.$scene_id.'"')->update();
            }
            $keywords = $qrcode_fun;
        }
        // 回复
        if (! empty($keywords)) {
            $keywords = html_in($keywords);
            //记录用户操作信息
            $this->record_msg($wedata['FromUserName'], $keywords);
            // 多客服
            $rs = $this->customer_service($wedata['FromUserName'], $keywords);
            if (empty($rs)) {
                // 功能插件
                $rs1 = $this->get_function($wedata['FromUserName'], $keywords);
                if (empty($rs1)) {
                    // 关键词回复
                    $rs2 = $this->keywords_reply($keywords);
                    if (empty($rs2)) {
                        //推荐商品
                        $rs_rec = $this->recommend_goods($wedata['FromUserName'], $keywords);
                        if(empty($rs_rec)){
                            // 消息自动回复
                            $this->msg_reply('msg');    
                        }
                    }
                }
            }
        }
    }

    /**
     * 关注处理
     *
     * @param array $info            
     */
    private function subscribe($openid = '', $scene_id = 0)
    {
        if(!empty($openid)){
            // 用户信息
            $info = $this->weObj->getUserInfo($openid);
            if (empty($info)) {
                $info = array();
            }
            
            // 查找用户是否存在
            $where['openid'] = $openid;
            $rs = $this->model->table('wechat_user')
                ->field('ect_uid, subscribe')
                ->where($where)
                ->find();
            // 未关注
            if (empty($rs)) {
                $ect_uid = 0;
                // 获取用户所在分组ID
                //$group_id = $this->weObj->getUserGroup($openid);
                $data['group_id'] = isset($info['groupid']) ? $info['groupid'] : $this->weObj->getUserGroup($openid);
                // 获取被关注公众号信息
                $data['wechat_id'] = $this->wechat_id;
                $data['subscribe'] = $info['subscribe'];
                $data['openid'] = $info['openid'];
                $data['nickname'] = $info['nickname'];
                $data['sex'] = $info['sex'];
                $data['city'] = $info['city'];
                $data['country'] = $info['country'];
                $data['province'] = $info['province'];
                $data['language'] = $info['country'];
                $data['headimgurl'] = $info['headimgurl'];
                $data['subscribe_time'] = $info['subscribe_time'];
                $data['remark'] = $info['remark'];
                $data['unionid'] = isset($info['unionid']) ? $info['unionid'] : '';
                $this->model->table('wechat_user')->data($data)->insert();
            }
            else {
                // 获取用户所在分组ID
                $data['group_id'] = isset($info['groupid']) ? $info['groupid'] : $this->weObj->getUserGroup($openid);
                // 获取被关注公众号信息
                $data['wechat_id'] = $this->wechat_id;
                $data['subscribe'] = $info['subscribe'];
                $data['openid'] = $info['openid'];
                $data['nickname'] = $info['nickname'];
                $data['sex'] = $info['sex'];
                $data['city'] = $info['city'];
                $data['country'] = $info['country'];
                $data['province'] = $info['province'];
                $data['language'] = $info['country'];
                $data['headimgurl'] = $info['headimgurl'];
                $data['subscribe_time'] = $info['subscribe_time'];
                $data['remark'] = $info['remark'];
                $data['unionid'] = isset($info['unionid']) ? $info['unionid'] : '';
                $this->model->table('wechat_user')->data($data)->where($where)->update();
            }
        }
    }

    /**
     * 取消关注
     *
     * @param string $openid            
     */
    public function unsubscribe($openid = '')
    {
        // 未关注
        $where['openid'] = $openid;
        $rs = $this->model->table('wechat_user')
            ->where($where)
            ->count();
        // 修改关注状态
        if ($rs > 0) {
            $data['subscribe'] = 0;
            $this->model->table('wechat_user')
                ->data($data)
                ->where($where)
                ->update();
        }
    }

    /**
     * 被动关注，消息回复
     *
     * @param string $type            
     * @param string $return            
     */
    private function msg_reply($type, $return = 0)
    {
        $replyInfo = $this->model->table('wechat_reply')
            ->field('content, media_id')
            ->where('type = "' . $type . '" and wechat_id = ' . $this->wechat_id)
            ->find();
        if (! empty($replyInfo)) {
            if (! empty($replyInfo['media_id'])) {
                $replyInfo['media'] = $this->model->table('wechat_media')
                    ->field('title, content, file, type, file_name')
                    ->where('id = ' . $replyInfo['media_id'])
                    ->find();
                if ($replyInfo['media']['type'] == 'news') {
                    $replyInfo['media']['type'] = 'image';
                }
                // 上传多媒体文件
                $rs = $this->weObj->uploadMedia(array(
                    'media' => '@' . ROOT_PATH . $replyInfo['media']['file']
                ), $replyInfo['media']['type']);
                
                // 回复数据重组
                if ($rs['type'] == 'image' || $rs['type'] == 'voice') {
                    $replyData = array(
                        'ToUserName' => $this->weObj->getRev()->getRevFrom(),
                        'FromUserName' => $this->weObj->getRev()->getRevTo(),
                        'CreateTime' => time(),
                        'MsgType' => $rs['type'],
                        ucfirst($rs['type']) => array(
                            'MediaId' => $rs['media_id']
                        )
                    );
                } elseif ('video' == $rs['type']) {
                    $replyData = array(
                        'ToUserName' => $this->weObj->getRev()->getRevFrom(),
                        'FromUserName' => $this->weObj->getRev()->getRevTo(),
                        'CreateTime' => time(),
                        'MsgType' => $rs['type'],
                        ucfirst($rs['type']) => array(
                            'MediaId' => $rs['media_id'],
                            'Title' => $replyInfo['media']['title'],
                            'Description' => strip_tags($replyInfo['media']['content'])
                        )
                    );
                }
                $this->weObj->reply($replyData);
                //记录用户操作信息
                $this->record_msg($this->weObj->getRev()->getRevTo(), '图文信息', 1);
            } else {
                // 文本回复
                $replyInfo['content'] = html_out($replyInfo['content']);
                if($replyInfo['content']){
                    $this->weObj->text($replyInfo['content'])->reply();
                    //记录用户操作信息
                    $this->record_msg($this->weObj->getRev()->getRevTo(), $replyInfo['content'], 1);
                }
            }
        }
    }

    /**
     * 关键词回复
     *
     * @param string $keywords            
     * @return boolean
     */
    private function keywords_reply($keywords)
    {
        $endrs = false;
        $sql = 'SELECT r.content, r.media_id, r.reply_type FROM ' . $this->model->pre . 'wechat_reply r LEFT JOIN ' . $this->model->pre . 'wechat_rule_keywords k ON r.id = k.rid WHERE k.rule_keywords = "' . $keywords . '" and r.wechat_id = ' . $this->wechat_id . ' order by r.add_time desc LIMIT 1';
        $result = $this->model->query($sql);
        if (! empty($result)) {
            // 素材回复
            if (! empty($result[0]['media_id'])) {
                $mediaInfo = $this->model->table('wechat_media')
                    ->field('id, title, content, digest, file, type, file_name, article_id, link')
                    ->where('id = ' . $result[0]['media_id'])
                    ->find();
                
                // 回复数据重组
                if ($result[0]['reply_type'] == 'image' || $result[0]['reply_type'] == 'voice') {
                    // 上传多媒体文件
                    $rs = $this->weObj->uploadMedia(array(
                        'media' => '@' . ROOT_PATH . $mediaInfo['file']
                    ), $result[0]['reply_type']);
                    
                    $replyData = array(
                        'ToUserName' => $this->weObj->getRev()->getRevFrom(),
                        'FromUserName' => $this->weObj->getRev()->getRevTo(),
                        'CreateTime' => time(),
                        'MsgType' => $rs['type'],
                        ucfirst($rs['type']) => array(
                            'MediaId' => $rs['media_id']
                        )
                    );
                    // 回复
                    $this->weObj->reply($replyData);
                    $endrs = true;
                } elseif ('video' == $result[0]['reply_type']) {
                    // 上传多媒体文件
                    $rs = $this->weObj->uploadMedia(array(
                        'media' => '@' . ROOT_PATH . $mediaInfo['file']
                    ), $result[0]['reply_type']);
                    
                    $replyData = array(
                        'ToUserName' => $this->weObj->getRev()->getRevFrom(),
                        'FromUserName' => $this->weObj->getRev()->getRevTo(),
                        'CreateTime' => time(),
                        'MsgType' => $rs['type'],
                        ucfirst($rs['type']) => array(
                            'MediaId' => $rs['media_id'],
                            'Title' => $replyInfo['media']['title'],
                            'Description' => strip_tags($replyInfo['media']['content'])
                        )
                    );
                    // 回复
                    $this->weObj->reply($replyData);
                    $endrs = true;
                } elseif ('news' == $result[0]['reply_type']) {
                    // 图文素材
                    $articles = array();
                    if (! empty($mediaInfo['article_id'])) {
                        $artids = explode(',', $mediaInfo['article_id']);
                        foreach ($artids as $key => $val) {
                            $artinfo = $this->model->table('wechat_media')
                                ->field('id, title, file, content, link')
                                ->where('id = ' . $val)
                                ->find();
                            //$artinfo['content'] = strip_tags(html_out($artinfo['content']));
                            $articles[$key]['Title'] = $artinfo['title'];
                            $articles[$key]['Description'] = $artinfo['digest'];
                            $articles[$key]['PicUrl'] = __URL__ . '/' . $artinfo['file'];
                            $articles[$key]['Url'] = empty($artinfo['link']) ? __HOST__ . url('article/wechat_news_info', array('id'=>$artinfo['id'])) : strip_tags(html_out($artinfo['link']));
                        }
                    } else {
                        $articles[0]['Title'] = $mediaInfo['title'];
                        //$articles[0]['Description'] = strip_tags(html_out($mediaInfo['content']));
                        $articles[0]['Description'] = $mediaInfo['digest'];
                        $articles[0]['PicUrl'] = __URL__ . '/' . $mediaInfo['file'];
                        $articles[0]['Url'] = empty($mediaInfo['link']) ? __HOST__ . url('article/wechat_news_info', array('id'=>$mediaInfo['id'])) : strip_tags(html_out($mediaInfo['link']));
                    }
                    // 回复
                    $this->weObj->news($articles)->reply();
                    //记录用户操作信息
                    $this->record_msg($this->weObj->getRev()->getRevTo(), '图文信息', 1);
                    $endrs = true;
                }
            } else {
                // 文本回复
                $result[0]['content'] = html_out($result[0]['content']);
                $this->weObj->text($result[0]['content'])->reply();
                //记录用户操作信息
                $this->record_msg($this->weObj->getRev()->getRevTo(), $result[0]['content'], 1);
                $endrs = true;
            }
        }
        return $endrs;
    }

    /**
     * 功能变量查询
     *
     * @param unknown $tousername            
     * @param unknown $fromusername            
     * @param unknown $keywords            
     * @return boolean
     */
    public function get_function($fromusername, $keywords)
    {
        $return = false;
        $rs = $this->model->table('wechat_extend')
            ->field('name, command, config')
            ->where('keywords like "%' . $keywords . '%" and enable = 1 and wechat_id = ' . $this->wechat_id)
            ->order('id asc')
            ->find();
        $file = ROOT_PATH . 'plugins/wechat/' . $rs['command'] . '/' . $rs['command'] . '.class.php';
        if (file_exists($file)) {
            require_once ($file);
            $wechat = new $rs['command']();
            $data = $wechat->show($fromusername, $rs);
            if (! empty($data)) {
                // 数据回复类型
                if ($data['type'] == 'text') {
                    $this->weObj->text($data['content'])->reply();
                    //记录用户操作信息
                    $this->record_msg($fromusername, $data['content'], 1);
                } elseif ($data['type'] == 'news') {
                    $this->weObj->news($data['content'])->reply();
                    //记录用户操作信息
                    $this->record_msg($fromusername, '图文消息', 1);
                }
                $return = true;
            }
        }
        return $return;
    }

    /**
     * 商品推荐查询
     *
     * @param unknown $tousername            
     * @param unknown $fromusername            
     * @param unknown $keywords            
     * @return boolean
     */
    public function recommend_goods($fromusername, $keywords)
    {
        $return = false;
        $rs = $this->model->table('wechat_extend')
            ->field('name, keywords, command, config')
            ->where('command = "recommend" and enable = 1 and wechat_id = ' . $this->wechat_id)
            ->order('id asc')
            ->find();

        $file = ROOT_PATH . 'plugins/wechat/' . $rs['command'] . '/' . $rs['command'] . '.class.php';
        if (file_exists($file)) {
            require_once ($file);
            $wechat = new $rs['command']();
            $rs['user_keywords'] = $keywords;
            $data = $wechat->show($fromusername, $rs);
            if (! empty($data)) {
                // 数据回复类型
                if ($data['type'] == 'text') {
                    $this->weObj->text($data['content'])->reply();
                    //记录用户操作信息
                    $this->record_msg($fromusername, $data['content'], 1);
                } elseif ($data['type'] == 'news') {
                    $this->weObj->news($data['content'])->reply();
                    //记录用户操作信息
                    $this->record_msg($fromusername, '图文消息', 1);
                }
                $return = true;
            }
        }
        return $return;
    }

    /**
     * 主动发送信息
     *
     * @param unknown $tousername            
     * @param unknown $fromusername            
     * @param unknown $keywords            
     * @param unknown $weObj            
     * @param unknown $return            
     * @return boolean
     */
    public function send_message($fromusername, $keywords, $weObj, $return = 0)
    {
        $result = false;
        $rs = $this->model->table('wechat_extend')
            ->field('name, command, config')
            ->where('keywords like "%' . $keywords . '%" and enable = 1 and wechat_id = ' . $this->wechat_id)
            ->order('id asc')
            ->find();
        $file = ROOT_PATH . 'plugins/wechat/' . $rs['command'] . '/' . $rs['command'] . '.class.php';
        if (file_exists($file)) {
            require_once ($file);
            $wechat = new $rs['command']();
            $data = $wechat->show($fromusername, $rs);
            if (! empty($data)) {
                if ($return) {
                    $result = $data;
                } else {
                    $weObj->sendCustomMessage($data['content']);
                    $result = true;
                }
            }
        }
        return $result;
    }

    /**
     * 多客服
     *
     * @param unknown $fromusername            
     * @param unknown $keywords            
     */
    public function customer_service($fromusername, $keywords)
    {
        /*$kfevent = $this->weObj->getRevKFClose();
        logResult(var_export($kfevent, true));*/
        $result = false;
        //是否超时
        $timeout = false;
        //查找用户
        $uid = $this->model->table('wechat_user')->field('uid')->where(array('openid'=>$fromusername))->getOne();
        if($uid){
            $time_list = $this->model->table('wechat_custom_message')->field('send_time')->where(array('uid'=>$uid))->order('send_time desc')->limit(2)->select();
            if($time_list[0]['send_time'] - $time_list[1]['send_time'] > 3600 * 2){
                $timeout = true;
            }

        }
        
        // 是否处在多客服流程
        $kefu = $this->model->table('wechat_user')
            ->field('openid')
            ->where('openid = "' . $fromusername . '"')
            ->getOne();
        if($kefu){
            if ($keywords == 'kefu') {
                $rs = $this->model->table('wechat_extend')
                    ->field('config')
                    ->where('command = "kefu" and enable = 1 and wechat_id = ' . $this->wechat_id)
                    ->getOne();
                if (! empty($rs)) {
                    $config = unserialize($rs);
                    $msg = array(
                        'touser' => $fromusername,
                        'msgtype' => 'text',
                        'text' => array(
                            'content' => '欢迎进入多客服系统'
                        )
                    );
                    $this->weObj->sendCustomMessage($msg);
                    //记录用户操作信息
                    $this->record_msg($fromusername, $msg['text']['content'], 1);

                    // 在线客服列表
                    $online_list = $this->weObj->getCustomServiceOnlineKFlist();
                    if ($online_list['kf_online_list']) {
                        foreach ($online_list['kf_online_list'] as $key => $val) {
                            if ($config['customer'] == $val['kf_account'] && $val['status'] > 0 && $val['accepted_case'] < $val['auto_accept']) {
                                $customer = $config['customer'];
                            } else {
                                $customer = '';
                            }
                        }
                    }
                    // 转发客服消息
                    $this->weObj->transfer_customer_service($customer)->reply();
                    $result = true;
                }
            } 
        }
        
        return $result;
    }

    /**
     * 获取用户昵称，头像
     *
     * @param unknown $user_id            
     * @return multitype:
     */
    static function get_avatar($user_id)
    {
        $u_row = model('Base')->model->table('wechat_user')
            ->field('nickname, headimgurl')
            ->where('ect_uid = ' . $user_id)
            ->find();
        if (empty($u_row)) {
            $u_row = array();
        }
        return $u_row;
    }

    /**
     * 微信OAuth操作
     */
    static function do_oauth()
    {
        // 默认公众号信息
        $wxinfo = model('Base')->model->table('wechat')->field('id, token, appid, appsecret, oauth_redirecturi, type, oauth_status')->find();
        if (! empty($wxinfo) && $wxinfo['type'] == 2) {
            $config['token'] = $wxinfo['token'];
            $config['appid'] = $wxinfo['appid'];
            $config['appsecret'] = $wxinfo['appsecret'];

            // 微信通验证
            $weObj = new Wechat($config);
            $_SESSION['wechat_user'] = empty($_SESSION['wechat_user']) ? array() : $_SESSION['wechat_user'];
            // 微信浏览器浏览
            if (is_wechat_browser() && $_SESSION['user_id'] === 0 && empty($_SESSION['wechat_user'])) {
                if(isset($_GET['code']) && !empty($_GET['code'])){
                    $token = $weObj->getOauthAccessToken();
                    $_SESSION['wechat_user'] = $weObj->getOauthUserinfo($token['access_token'], $token['openid']); //用户数据
                }

                if(empty($_SESSION['wechat_user'])){
                    $_SESSION['redirect_url'] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
                    //$auth = $weObj->getOauthRedirect($_SESSION['redirect_url'], '1', 'snsapi_base');
                    $auth = $weObj->getOauthRedirect($_SESSION['redirect_url'], '1');
                    header('location: '. $auth);
                    exit();
                }
            }
			
            $flag = I('get.flag');
            //授权登录
            if (($_SESSION['user_id'] === 0 && !empty($_SESSION['wechat_user']) && CONTROLLER_NAME !='Wechat' && empty($_SESSION['openid']) && !isset($_SESSION['redirect_user'])) || $flag == 'oauth'){
                if($wxinfo['oauth_status'] == '1' || $flag == 'oauth'){
                    //self::update_weixin_user($_SESSION['wechat_user'], $wxinfo['id'], $weObj);
                    $haspc = file_exists('../data/config.php') ? 1 : 0;
                    self::do_user($_SESSION['wechat_user'], $wxinfo['id'], $weObj, 1, $haspc);
                    header('location: '. $_SESSION['redirect_url']);
                    exit();
                }
                else{
                    $haspc = file_exists('../data/config.php') ? 1 : 0;
                    self::do_user($_SESSION['wechat_user'], $wxinfo['id'], $weObj, 0, $haspc);
                }
            }
        }
    }

    /**
     * 用户处理
     * @param  [type]  $wechat_user [description]
     * @param  [type]  $wechat_id   [description]
     * @param  [type]  $weObj       [description]
     * @param  [type]  $isoauth     [是否开启自动登录]
     * @param  integer $haspc       [是否有pc端]
     * @return [type]               [description]
     *
     * $userinfo = array('openid', 'nickname', 'sex', 'language', 'city', 'province', 'country', 'headimgurl', 'privilege', 'unionid');
     */
    static function do_user($userinfo, $wechat_id, $weObj, $isoauth = 0, $haspc = 0){
    	$user = model('Base')->model->table('wechat_user')->field('openid, ect_uid')->where('openid = "' . $userinfo['openid'] . '"')->find();
    	if($isoauth && $haspc){
    		// 有pc,开启自动登录
    		self::do_oauth_user($userinfo, $wechat_id, $weObj, $user, $isoauth);
    	}
    	elseif($isoauth && empty($haspc)){
    		//没有pc,开启自动登录
    		self::update_weixin_user($userinfo, $wechat_id, $weObj);
    	}
    	elseif(empty($isoauth) && $haspc){
			//有pc,没开启自动登录
			self::do_oauth_user($userinfo, $wechat_id, $weObj, $user);
    	}
    	elseif(empty($isoauth) && empty($haspc)){
    		//没有pc,没开启自动登录
    		self::do_oauth_user($userinfo, $wechat_id, $weObj, $user);
    	}
    }

    /**
     * 自动登录操作
     * @param  [type]  $userinfo  [description]
     * @param  [type]  $wechat_id [description]
     * @param  [type]  $weObj     [description]
     * @param  [type]  $user      [description]
     * @param  integer $isoauth   [description]
     * @return [type]             [description]
     */
    static function do_oauth_user($userinfo, $wechat_id, $weObj, $user, $isoauth = 0){
    	$user_url = __HOST__.url('user/bind');
    	if(empty($user)){
			$group_id = $weObj->getUserGroup($userinfo['openid']);
        	$group_id = $group_id ? $group_id : 0;
			//微信用户绑定会员id
            $ect_uid = isset($user['ect_uid']) && !empty($user['ect_uid']) ? $user['ect_uid'] : 0;
            //查看公众号是否绑定
            if(isset($userinfo['unionid'])){
                $ect_uid = model('Base')->model->table('wechat_user')->field('ect_uid')->where(array('unionid'=>$userinfo['unionid']))->getOne();
            }

            $data1['ect_uid'] = $ect_uid;
            $data1['wechat_id'] = $wechat_id;
            $data1['subscribe'] = 0;
            $data1['openid'] = $userinfo['openid'];
            $data1['nickname'] = $userinfo['nickname'];
            $data1['sex'] = $userinfo['sex'];
            $data1['city'] = $userinfo['city'];
            $data1['country'] = $userinfo['country'];
            $data1['province'] = $userinfo['province'];
            $data1['language'] = $userinfo['language'];
            $data1['headimgurl'] = $userinfo['headimgurl'];
            $data1['subscribe_time'] = '';
            $data1['group_id'] = $group_id;
            $data1['unionid'] = isset($userinfo['unionid']) ? $userinfo['unionid'] : '';
            
            model('Base')->model->table('wechat_user')->data($data1)->insert();
            //未绑定用户
            if(empty($ect_uid)){
                $_SESSION['redirect_user'] = 1;
            	//会员中心注册绑定
            	header("Location:".$user_url);
            	exit;
            }
            
		}
        elseif(!empty($user) && $user['ect_uid'] == 0){
            //会员中心注册绑定
            $_SESSION['redirect_user'] = 1;
            header("Location:".$user_url);
            exit;
        }
		elseif($user['ect_uid'] > 0 && $isoauth){
            $userinfo['group_id'] = isset($userinfo['groupid']) ? $userinfo['groupid'] : $weObj->getUserGroup($userinfo['openid']);
            unset($userinfo['groupid']);
            $userinfo['privilege'] = isset($userinfo['privilege']) ? serialize($userinfo['privilege']) : '';
            model('Base')->model->table('wechat_user')
                ->data($userinfo)
                ->where('openid = "' . $userinfo['openid'] . '"')
                ->update();
            $new_user_name = model('Base')->model->table('users')
                ->field('user_name')
                ->where('user_id = "' . $user['ect_uid'] . '"')
                ->getOne();
            ECTouch::user()->set_session($new_user_name);
            ECTouch::user()->set_cookie($new_user_name);
            model('Users')->update_user_info();
            // 推送量
	        model('Base')->model->table('wechat')->data('oauth_count = oauth_count + 1')->where('default_wx = 1 and status = 1')->update();
		}
        session('openid', $userinfo['openid']);
    }

    /**
     * 用户注册登录后绑定
     * @return [type] [description]
     */
    static function do_bind(){
    	if(!empty($_SESSION['user_id']) && !empty($_SESSION['wechat_user'])){
    		$condition['openid'] = $_SESSION['wechat_user']['openid'];
    		$user = model('Base')->model->table('wechat_user')->field('openid, ect_uid')->where($condition)->find();
    		if($user && empty($user['ect_uid'])){
                //用户是否绑定过
                $isbind = model('Base')->model->table('wechat_user')->where(array('ect_uid'=>$_SESSION['user_id']))->count();
                if($isbind == 0){
                    model('Base')->model->table('wechat_user')->data(array('ect_uid'=>$_SESSION['user_id']))->where($condition)->update();
                }
    		}
    	}
    }

    /**
     * 更新微信用户信息
     *  
     * @param unknown $userinfo            
     * @param unknown $wechat_id            
     * @param unknown $weObj       
     */
    static function update_weixin_user($userinfo, $wechat_id, $weObj)
    {
        $time = time();
        $ret = model('Base')->model->table('wechat_user')
            ->field('openid, ect_uid')
            ->where('openid = "' . $userinfo['openid'] . '"')
            ->find();
        if (empty($ret) || (!empty($ret) && $ret['ect_uid'] == 0)) {
            // 获取用户所在分组ID
            $group_id = $weObj->getUserGroup($userinfo['openid']);
            $group_id = $group_id ? $group_id : 0;
            //微信用户绑定会员id
            $ect_uid = 0;
            //查看公众号是否绑定
            if(isset($userinfo['unionid'])){
                $ect_uid = model('Base')->model->table('wechat_user')->field('ect_uid')->where(array('unionid'=>$userinfo['unionid']))->getOne();
            }

            //未绑定
            if(empty($ect_uid)){
                // 设置的用户注册信息
                $register = model('Base')->model->table('wechat_extend')
                    ->field('config')
                    ->where('enable = 1 and command = "register_remind" and wechat_id = '.$wechat_id)
                    ->find();
                if (! empty($register)) {
                    $reg_config = unserialize($register['config']);
                    $username = msubstr($reg_config['user_pre'], 3, 0, 'utf-8', false) . time().mt_rand(1, 99);
                    // 密码随机数
                    $rs = array();
                    $arr = range(0, 9);
                    $reg_config['pwd_rand'] = $reg_config['pwd_rand'] ? $reg_config['pwd_rand'] : 3;
                    for ($i = 0; $i < $reg_config['pwd_rand']; $i ++) {
                        $rs[] = array_rand($arr);
                    }
                    $pwd_rand = implode('', $rs);
                    // 密码
                    $password = $reg_config['pwd_pre'] . $pwd_rand;
                    // 通知模版
                    $template = str_replace(array(
                        '[$username]',
                        '[$password]'
                    ), array(
                        $username,
                        $password
                    ), $reg_config['template']);
                } else {
                    $username = 'wx_' . time().mt_rand(1, 99);
                    $password = 'ecmoban';
                    // 通知模版
                    $template = '默认用户名：' . $username . "\r\n" . '默认密码：' . $password;
                }
                // 会员注册
                $domain = get_top_domain();
                $other = array(
                    'parent_id' =>  $_SESSION['parent_id'] ? $_SESSION['parent_id'] : 0,
                );
                if (model('Users')->register($username, $password, $username . '@' . $domain,$other) !== false) {
                    $data['user_rank'] = 99;
                    
                    model('Base')->model->table('users')
                        ->data($data)
                        ->where('user_name = "' . $username . '"')
                        ->update();
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

            if(!empty($ret)){
                $userinfo['group_id'] = isset($userinfo['groupid']) ? $userinfo['groupid'] : $weObj->getUserGroup($userinfo['openid']);
                unset($userinfo['groupid']);
                $userinfo['privilege'] = isset($userinfo['privilege']) ? serialize($userinfo['privilege']) : '';
                $userinfo['ect_uid'] = $data1['ect_uid'];
                model('Base')->model->table('wechat_user')->data($userinfo)->where('openid = "' . $userinfo['openid'] . '"')->update();
            }
            else{
                $data1['wechat_id'] = $wechat_id;
                $data1['subscribe'] = 0;
                $data1['openid'] = $userinfo['openid'];
                $data1['nickname'] = $userinfo['nickname'];
                $data1['sex'] = $userinfo['sex'];
                $data1['city'] = $userinfo['city'];
                $data1['country'] = $userinfo['country'];
                $data1['province'] = $userinfo['province'];
                $data1['language'] = $userinfo['language'];
                $data1['headimgurl'] = $userinfo['headimgurl'];
                $data1['subscribe_time'] = '';
                $data1['group_id'] = $group_id;
                $data1['unionid'] = $userinfo['unionid'];

                model('Base')->model->table('wechat_user')->data($data1)->insert();
            }

            // 微信端发送消息
            /*$msg = array(
                'touser' => $userinfo['openid'],
                'msgtype' => 'text',
                'text' => array(
                    'content' => $template
                )
            );
            $weObj->sendCustomMessage($msg);*/
        }
        else {
            //开放平台有privilege字段,公众平台没有
            $userinfo['group_id'] = isset($userinfo['groupid']) ? $userinfo['groupid'] : $weObj->getUserGroup($userinfo['openid']);
            unset($userinfo['groupid']);
            $userinfo['privilege'] = isset($userinfo['privilege']) ? serialize($userinfo['privilege']) : '';
            model('Base')->model->table('wechat_user')
                ->data($userinfo)
                ->where('openid = "' . $userinfo['openid'] . '"')
                ->update();
            $new_user_name = model('Base')->model->table('users')
                ->field('user_name')
                ->where('user_id = "' . $ret['ect_uid'] . '"')
                ->getOne();
            ECTouch::user()->set_session($new_user_name);
            ECTouch::user()->set_cookie($new_user_name);
            model('Users')->update_user_info();
        }
        // 推送量
        model('Base')->model->table('wechat')
            ->data('oauth_count = oauth_count + 1')
            ->where('default_wx = 1 and status = 1')
            ->update();
        
        session('openid', $userinfo['openid']);
    }
    
    /**
     * 记录用户操作信息
     */
     public function record_msg($fromusername, $keywords, $iswechat = 0){
        $uid = $this->model->table('wechat_user')->field('uid')->where(array('openid'=>$fromusername))->getOne();
        if($uid){
            $data['uid'] = $uid;
            $data['msg'] = $keywords;
            $data['send_time'] = time();
            //是公众号回复
            if($iswechat){
                $data['iswechat'] = 1;
            }
            $this->model->table('wechat_custom_message')
                ->data($data)
                ->insert();
        }
     }

    /**
     * 检查是否是微信浏览器访问
     */
    static function is_wechat_browser()
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($user_agent, 'MicroMessenger') === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 插件页面显示方法
     *
     * @param string $plugin            
     */
    public function plugin_show()
    {
        $plugin = I('get.name');
        $file = ADDONS_PATH . 'wechat/' . $plugin . '/' . $plugin . '.class.php';
        if (file_exists($file)) {
            include_once ($file);
            $wechat = new $plugin();
            $wechat->html_show();
        }
    }

    /**
     * 插件处理方法
     *
     * @param string $plugin            
     */
    public function plugin_action()
    {
        $plugin = I('get.name');
        $file = ADDONS_PATH . 'wechat/' . $plugin . '/' . $plugin . '.class.php';
        if (file_exists($file)) {
            include_once ($file);
            $wechat = new $plugin();
            $wechat->action();
        }
    }

    /**
     * 获取公众号配置
     *
     * @param string $orgid            
     * @return array
     */
    private function get_config($orgid)
    {
        $config = $this->model->table('wechat')
            ->field('id, token, appid, appsecret')
            ->where('orgid = "' . $orgid . '" and status = 1')
            ->find();
        if (empty($config)) {
            $config = array();
        }
        return $config;
    }

    /**
     * 获取access_token的接口
     * @return [type] [description]
     */
    public function check_auth(){
        $appid = I('get.appid');
        $appsecret = I('get.appsecret');
        if(empty($appid) || empty($appsecret)){
            echo json_encode(array('errmsg'=>'信息不完整，请提供完整信息', 'errcode'=>1));   
            exit;
        }
        $config = $this->model->table('wechat')
            ->field('token, appid, appsecret')
            ->where('appid = "' . $appid . '" and appsecret = "'.$appsecret.'" and status = 1')
            ->find();
        if(empty($config)){
            echo json_encode(array('errmsg'=>'信息错误，请检查提供的信息', 'errcode'=>1));
            exit;   
        }

        $obj = new Wechat($config);
        $access_token = $obj->checkAuth();
        if($access_token){
          echo json_encode(array('access_token'=>$access_token, 'errcode'=>0));
          exit; 
        }
        else{
          echo json_encode(array('errmsg'=>$obj->errmsg, 'errcode'=>$obj->errcode));    
          exit;
        }
    }

    /**
     * 推荐分成二维码
     * @param  string  $user_name [description]
     * @param  integer $user_id   [description]
     * @param  integer $time      [description]
     * @param  string  $fun       [description]
     * @return [type]             [description]
     */
    static function rec_qrcode($user_name = '', $user_id = 0, $expire_seconds = 0, $fun = ''){
        if(empty($user_id)){
            return false;
        }
        // 默认公众号信息
        $wxinfo = model('Base')->model->table('wechat')->field('id, token, appid, appsecret, oauth_redirecturi, type, oauth_status')->where('default_wx = 1 and status = 1')->find();

        if (! empty($wxinfo) && $wxinfo['type'] == 2) {
            $config['token'] = $wxinfo['token'];
            $config['appid'] = $wxinfo['appid'];
            $config['appsecret'] = $wxinfo['appsecret'];
            // 微信通验证
            $weObj = new Wechat($config);

            $qrcode = model('Base')->model->table('wechat_qrcode')->field('id, scene_id, type, expire_seconds, qrcode_url')->where(array('scene_id'=>$user_id, 'wechat_id'=>$wxinfo['id']))->find();
            if($qrcode['id'] && !empty($qrcode['qrcode_url'])){

                return $qrcode['qrcode_url'];
            }
            elseif($qrcode['id'] && empty($qrcode['qrcode_url'])){
                $ticket = $weObj->getQRCode((int)$qrcode['scene_id'], $qrcode['type'], $qrcode['expire_seconds']);
                if (empty($ticket)) {
                    //$weObj->errCode, $weObj->errMsg
                    return false;
                }
                $data['ticket'] = $ticket['ticket'];
                $data['expire_seconds'] = $ticket['expire_seconds'];
                $data['endtime'] = time() + $ticket['expire_seconds'];
                // 二维码地址
                $data['qrcode_url'] = $weObj->getQRUrl($ticket['ticket']);
                M()->table('wechat_qrcode')->data($data)->where(array('id'=>$qrcode['id']))->update();

                return $data['qrcode_url'];
            }
            else{
                $data['function'] = $fun;
                $data['scene_id'] = $user_id;
                $data['username'] = $user_name;
                $data['type'] = empty($expire_seconds) ? 1 : 0;
                $data['wechat_id'] = $wxinfo['id'];
                $data['status'] = 1;
                //生成二维码
                $ticket = $weObj->getQRCode((int)$data['scene_id'], $data['type'], $expire_seconds);
                if (empty($ticket)) {
                    //$weObj->errCode, $weObj->errMsg
                    return false;
                }
                $data['ticket'] = $ticket['ticket'];
                $data['expire_seconds'] = $ticket['expire_seconds'];
                $data['endtime'] = time() + $ticket['expire_seconds'];
                // 二维码地址
                $data['qrcode_url'] = $weObj->getQRUrl($ticket['ticket']);

                M()->table('wechat_qrcode')->data($data)->insert();

                return $data['qrcode_url'];
            }
        }
        return false;
    }
}