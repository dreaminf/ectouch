<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：bd.class.php
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
 * 精品查询类
 *
 * @author wanglu
 *        
 */
class bd extends PluginWechatController
{
    // 插件名称
    protected $plugin_name = '';
    // 配置
    protected $cfg = array();

    /**
     * 构造方法
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
        $this->plugin_display('install', $this->cfg);
    }

    /**
     * 获取数据
     * @see PluginWechatController::show()
     */
    public function show($fromusername, $info)
    {
        $articles = array();
        $config = unserialize($info['config']);
        // 素材
        if (! empty($config['media_id'])) {
            $media = model('Base')->model->table('wechat_media')
            ->field('id, title, file, file_name, type, content, add_time, article_id, link')
            ->where('id = ' . $config['media_id'])
            ->find();
            // 单图文
            if (empty($media['article_id'])) {
                $media['content'] = strip_tags(html_out($media['content']));
            }
        }
        if (! empty($media)) {
            // 数据
            $articles[0]['Title'] = $config['media']['title'];
            $articles[0]['Description'] = $media['content'];
            // 不是远程图片
            if (! preg_match('/(http:|https:)/is', $media['file'])) {
                $articles[0]['PicUrl'] = __URL__ . '/' . $media['file'];
            } else {
                $articles[0]['PicUrl'] = $media['file'];
            }
            $articles[0]['Url'] = html_out($media['link']);
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
            // 配置信息
            $config = array();
            $config = unserialize($info['config']);
            // 开启积分赠送
            if (isset($config['point_status']) && $config['point_status'] == 1) {
                $where = 'openid = "' . $fromusername . '" and keywords = "' . $info['keywords'] . '" and createtime > (UNIX_TIMESTAMP(NOW())- ' . $config['point_interval'] . ')';
                $num = model('base')->model->table('wechat_point')
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
    public function html_show(){
        $file = ROOT_PATH . 'plugins/wechat/' . $this->plugin_name . '/view/index.php';
        if (file_exists($file)) {
            require_once ($file);
        }
    }

    /**
     * 行为操作
     */
    public function action()
    {}
}
