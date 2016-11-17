<?php

namespace apps\weapp\services;

class AuthService{
    private $common;
    private $model;
    private $appTable;
    private $appTableItem;

    public function __construct($data = null)
    {
        $this->common = $data['common'];
        $this->model = $data['model'];

        $this->appTable = 'wechat_widget';
        $this->appTableItem = 'wechat_widget_item';
    }

    /**
     * 获取微信小程序表名
     */
    public function getTableApp(){
        return $this->appTable;
    }
    /**
     * 获取微信小程序表名
     */
    public function getTableAppItem(){
        return $this->appTableItem;
    }

    /**
     * 权限验证
     * @appId 小程序对应ID
     * @openId  用户对应小程序ID
     * 没有token 则发起认证机制
     */
    public function authenticate(){
        return array('union_id'=>1, 'widget_id'=>1);
        //获取需要认证列表
        if(!$this->getAccessList()){
            return ;
        }
        $request = $this->common->get();
        $array = array('code'=>0, 'msg'=>'');

        $accessToken = null;
        $refreshToken = null;
        //验证请求
        if(empty($request)){
            $array['code'] = 1;
            $array['msg'] = '没有获取到数据';
            $this->common->responseAct($array);
        }
        //验证token
        if(isset($request['access_token']) && !empty($request['access_token'])){
            $accessToken = $request['access_token'];
        }
        else{
            $array['code'] = 1;
            $array['msg'] = '没有获取到token';
            $this->common->responseAct($array);
        }
        //验证是否存在
        $data['access_token'] = $accessToken;
        $isToken = $this->model->table($this->appTableItem)->where($data)->order('access_token_expire')->find();
        if(!$isToken){
            $array['code'] = 1;
            $array['msg'] = '认证失败';
            $this->common->responseAct($array);
        }
        //验证是否过期
        if($isToken['access_token_expire'] < gmtime()){
            $array['code'] = 1;
            $array['msg'] = '验证过期';
            $this->common->responseAct($array);
        }
        $token = $this->common->get('token');

        $getTokenInfo = $this->model->table($this->appTable)->where('token=' . $token)->fetchSql()->find();
        return $getTokenInfo;
    }

    /**
     * 判断当前页面是否需要验证
     */
    private function getAccessList(){
        $config = C('acl');//获取配置文件
        $controller = strtolower(CONTROLLER_NAME);

        return $config[$controller][0];
    }

    /**
     * 生成随机字符串
     */
    public function randStr($n = 6){
        $source = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $str = null;
        $i = 0;
        while ($i < $n){
            $str = substr(strlen($source)-1, 1);
            $i++;
        }
        return (string)$str;
    }

    /**
     * 生成access_token
     */
    public function generateAccessToken(){
        //判断token代码缓存
        $cookie = $_COOKIE['access_token_array'];
        if($cookie) return json_decode($cookie);

        //生成token代码
        $timestamp = (string)gmtime();
        $nonce = (string)rand(1000, 9999);
        $echostr = $this->randStr();

        $code = $timestamp.$nonce.$echostr;
        $accessToken = sha1($code);

        $expire = (int)$timestamp + 7200;
        $tokenArray = array('token'=>$accessToken, 'expire'=>$expire);
        return $tokenArray;
    }

    /**
     * 添加数据到数据库
     */
    public function generateAuthInfo($data){
        $array = array('error'=>0);

        //判断是否存在这个小程序
        $widgetInfo = $this->model->table($this->appTable)->where('app_id='.$data['widget_id'])->count('id');
        if(empty($widgetInfo)){
            $array['error'] = 1;
            $array['msg'] = '不存在';
            die(json_encode($array));
        }

        //检查是否存在这个用户
        $user = $this->model->table($this->appTableItem)->field('id')->where('union_id='.$data['union_id'])->find();
        if(count($user) > 0){
            $res = $this->model->table($this->appTableItem)->where('id='.$user['id'])->save($data);

            if(!$res){
                $array['error'] = 1;
                $array['msg'] = 'update db failed';
                logResult('wx_widget_generateAuthInfo:' . json_encode($array));
                die(json_encode($array));
            }
            return $res;
        }else{
            $res = $this->model->table($this->appTableItem)->add($data);

            if(!$res){
                $array['error'] = 1;
                $array['msg'] = 'insert db failed';
                logResult('wx_widget_generateAuthInfo:' . json_encode($array));
                die(json_encode($array));
            }
            return $res;
        }


    }

}