<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：news.php
 * ----------------------------------------------------------------------------
 * 功能描述：微信通-精品查询
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */

/* 访问控制 */
if (! defined('IN_ECTOUCH')) {
    die('Deny Access');
}

/**
 * 砸金蛋
 *
 * @author wanglu
 *        
 */
class zjd extends PluginWechatController
{
    // 插件名称
    protected $plugin_name = '';
    // 配置
    protected $cfg = array();

    /**
     * 构造方法
     *
     * @param unknown $cfg            
     */
    public function __construct($cfg = array())
    {
        $name = basename(__FILE__, '.class.php');
        $this->plugin_name = $name;
        $this->cfg = $cfg;
    }

    /**
     * 安装
     */
    public function install()
    {
        // 编辑
        if (! empty($this->cfg['handler']) && is_array($this->cfg['config'])) {
            // 素材
            if (! empty($this->cfg['config']['media_id'])) {
                $media = model('Base')->model->table('wechat_media')
                    ->field('id, title, file, file_name, type, content, add_time, article_id')
                    ->where('id = ' . $this->cfg['config']['media_id'])
                    ->find();
                // 单图文
                if (empty($media['article_id'])) {
                    $media['content'] = strip_tags(html_out($media['content']));
                    $this->cfg['config']['media'] = $media;
                }
            }
            // url处理
            if (! empty($this->cfg['config']['plugin_url'])) {
                $this->cfg['config']['plugin_url'] = html_out($this->cfg['config']['plugin_url']);
            }
            // 奖品处理
            if (is_array($this->cfg['config']['prize_level']) && is_array($this->cfg['config']['prize_count']) && is_array($this->cfg['config']['prize_prob']) && is_array($this->cfg['config']['prize_name'])) {
                foreach ($this->cfg['config']['prize_level'] as $key => $val) {
                    $this->cfg['config']['prize'][] = array(
                        'prize_level' => $val,
                        'prize_name' => $this->cfg['config']['prize_name'][$key],
                        'prize_count' => $this->cfg['config']['prize_count'][$key],
                        'prize_prob' => $this->cfg['config']['prize_prob'][$key]
                    );
                }
            }
        }
        $this->plugin_display('install', $this->cfg);
    }

    /**
     * 获取数据
     */
    public function show()
    {
        $articles = array();
        // 插件配置
        $config = $this->get_config($this->plugin_name);
        // 页面信息
        if (isset($config['media']) && ! empty($config['media'])) {
            // 数据
            $articles[0]['Title'] = $config['media']['title'];
            $articles[0]['Description'] = $config['media']['content'];
            // 不是远程图片
            if (! preg_match('/(http:|https:)/is', $config['media']['file'])) {
                $articles[0]['PicUrl'] = __URL__ . '/' . $config['media']['file'];
            } else {
                $articles[0]['PicUrl'] = $config['media']['file'];
            }
            $articles[0]['Url'] = html_out($config['media']['link']);
        }
        
        return $articles;
    }

    /**
     * 积分赠送
     *
     * @param unknown $fromusername            
     * @param unknown $info            
     */
    public function give_point($fromusername, $info)
    {
        if (! empty($info)) {
            $info['config'] = unserialize($info['config']);
            // 配置信息重组
            $config = array();
            foreach ($info['config'] as $key => $val) {
                $config[$val['name']] = $val['value'];
            }
            // 开启积分赠送
            if (isset($config['point_status']) && $config['point_status'] == 1) {
                $where = 'openid = "' . $fromusername . '" and keywords = "' . $info['keywords'] . '" and createtime > (UNIX_TIMESTAMP(NOW())- ' . $config['point_interval'] . ')';
                $num = model('Base')->model->table('wechat_point')
                    ->field('createtime')
                    ->where($where)
                    ->order('createtime desc')
                    ->count();
                // 当前时间减去时间间隔得到的历史时间之后赠送的次数
                if ($num < $config['point_num']) {
                    $this->do_point($fromusername, $info, $config['point_value']);
                }
            }
        }
    }

    /**
     * 页面显示
     */
    public function html_show()
    {
        // 插件配置
        $config = $this->get_config($this->plugin_name);
        if(!empty($config)){
            $num = count($config['prize']);
            foreach($config['prize'] as $key=>$val){
                //删除最后一项未中奖
                if($key == ($num-1)){
                    unset($config['prize'][$key]);
                }
            }
        }

        $starttime = strtotime($config['starttime']);
        $endtime = strtotime($config['endtime']);
        //用户抽奖剩余的次数
        $openid = session('openid');
        $count = model('Base')->model->table('wechat_prize')->where('openid = "'.$openid.'" and dateline between "'.$starttime.'" and "'.$endtime.'"')->count();
        $config['prize_num'] = $config['prize_num'] - $count;
        //中奖记录
        $sql = 'SELECT u.nickname, p.prize_name, p.id FROM '.model('Base')->model->pre.'wechat_prize p LEFT JOIN '.model('Base')->model->pre.'wechat_user u ON p.openid = u.openid where p.openid = "'.$openid.'" and dateline between "'.$starttime.'" and "'.$endtime.'" and p.prize_type = 1 ORDER BY dateline desc';
        $list = model('Base')->model->query($sql);
        
        $file = ROOT_PATH . 'plugins/wechat/' . $this->plugin_name . '/view/index.php';
        if (file_exists($file)) {
            require_once ($file);
        }
    }

    /**
     * 行为操作
     */
    public function action()
    {
        if(IS_POST){
            $data = I('post.data');
            $winner = serialize($data);
            exit;
        }
        //获奖用户资料填写页面
        if(IS_GET && !IS_AJAX){
            $id = I('get.id');
            $file = ROOT_PATH . 'plugins/wechat/' . $this->plugin_name . '/view/user_info.php';
            if (file_exists($file)) {
                require_once ($file);
            } 
            exit;
        }
        //抽奖操作
        if(IS_GET && IS_AJAX){
            $rs = array();
            //登录
            $openid = session('openid');
            if(empty($openid)){
                $rs['msg'] = 0;
                $rs['yes'] = '请先登录';
                echo json_encode($rs);
                exit;
            } 
            
            // 插件配置
            $config = $this->get_config($this->plugin_name);
            //活动过期
            $starttime = strtotime($config['starttime']);
            $endtime = strtotime($config['endtime']);
            if(time() < $starttime){
                $rs['msg'] = 2;
                $rs['yes'] = '活动未开始';
                echo json_encode($rs);
                exit;
            }
            if(time() > $endtime){
                $rs['msg'] = 2;
                $rs['yes'] = '活动已结束';
                echo json_encode($rs);
                exit;
            }
            //超过次数
            if(!empty($openid)){
                $num = model('Base')->model->table('wechat_prize')->where('openid = "'.$openid.'" and dateline between "'.$starttime.'" and "'.$endtime.'"')->count();
                if($num <= 0){
                    $num = 1;
                }
                else{
                    $num = $num + 1;
                }
            }
            else{
                $num = 1;
            }
            
            if($num > $config['prize_num']){
                $rs['msg'] = 2;
                $rs['yes'] = '你已经用光了抽奖次数';
                echo json_encode($rs);
                exit;
            }
            
            $prize = $config['prize'];
            if(!empty($prize)){
                $arr = array();
                $prize_name = array();
                foreach($prize as $key=>$val){
                    $arr[$val['prize_level']] = $val['prize_prob'];
                    $prize_name[$val['prize_level']] = $val['prize_name'];
                }
            }
            $lastarr = end($prize);
            //获取中奖项
            $level = $this->get_rand($arr);
            //0为未中奖,1为中奖
            if($level == $lastarr['prize_level']){
                $rs['msg'] = 0;
                $data['prize_type'] = 0;
            }
            else{
                $rs['msg'] = 1;
                $data['prize_type'] = 1;
            }
            $rs['yes'] = $prize_name[$level];
            $rs['num'] = $config['prize_num'] - $num;
            //抽奖记录
            $data['openid'] = $openid;
            $data['prize_name'] = $prize_name[$level];
            $data['dateline'] = time();
            $id = model('Base')->model->table('wechat_prize')->data($data)->insert();
            if($level != $lastarr['prize_level']){
                //获奖链接
                $rs['link'] = url('wechat/plugin_action', array('name'=>$this->plugin_name, 'id'=>$id));
            }
            
            echo json_encode($rs);
            exit;
        }
    }
    
    /**
     * 中奖概率计算
     * @param unknown $proArr
     * @return Ambigous <string, unknown>
     */
    function get_rand($proArr)
    {
        $result = '';
        // 概率数组的总概率精度
        $proSum = array_sum($proArr);
        // 概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset($proArr);
        return $result;
    }
    
    /**
     * 获取插件配置信息
     *
     * @param string $code            
     * @return multitype:unknown
     */
    private function get_config($code = '')
    {
        // 默认公众号信息
        $config = array();
        $wxid = model('Base')->model->table('wechat')
            ->field('id')
            ->where('default_wx = 1')
            ->getOne();
        if (! empty($wxid)) {
            $plugin_config = model('Base')->model->table('wechat_extend')
                ->field('config')
                ->where('wechat_id = ' . $wxid . ' and command = "' . $code . '" and enable = 1')
                ->getOne();
            if (! empty($plugin_config)) {
                $config = unserialize($plugin_config);
                // 素材
                if (! empty($config['media_id'])) {
                    $media = model('Base')->model->table('wechat_media')
                        ->field('id, title, file, file_name, type, content, add_time, article_id, link')
                        ->where('id = ' . $config['media_id'])
                        ->find();
                    // 单图文
                    if (empty($media['article_id'])) {
                        $media['content'] = strip_tags(html_out($media['content']));
                        $config['media'] = $media;
                    }
                }
                // url处理
                if (! empty($config['plugin_url'])) {
                    $config['plugin_url'] = html_out($config['plugin_url']);
                }
                // 奖品处理
                if (is_array($config['prize_level']) && is_array($config['prize_count']) && is_array($config['prize_prob']) && is_array($config['prize_name'])) {
                    foreach ($config['prize_level'] as $key => $val) {
                        $config['prize'][] = array(
                            'prize_level' => $val,
                            'prize_name' => $config['prize_name'][$key],
                            'prize_count' => $config['prize_count'][$key],
                            'prize_prob' => $config['prize_prob'][$key]
                        );
                    }
                }
            }
        }
        return $config;
    }
}
