<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：ExtendController.class.php
 * ----------------------------------------------------------------------------
 * 功能描述：微信公众平台扩展
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */
class ExtendController extends AdminController
{

    public $plugin_type = 'wechat';

    public $plugin_name = '';

    public function __construct()
    {
        parent::__construct();
        $this->plugin_name = I('get.ks');
        autoload('PluginWechatController');
    }

    /**
     * 功能变量列表
     */
    public function index()
    {
        // 数据库中的数据
        $extends = $this->model->table('wechat_extend')
            ->field('name, keywords, command, config, enable, author, website')
            ->where('type = "function" and enable = 1 and wechat_id = ' . session('wechat_id'))
            ->order('id asc')
            ->select();
        if (! empty($extends)) {
            $kw = array();
            foreach ($extends as $key => $val) {
                $val['config'] = unserialize($val['config']);
                $kw[$val['command']] = $val;
            }
        }
        
        $modules = $this->read_wechat();
        if (! empty($modules)) {
            foreach ($modules as $k => $v) {
                $ks = $v['command'];
                // 数据库中存在，用数据库的数据
                if (isset($kw[$v['command']])) {
                    $modules[$k]['keywords'] = $kw[$ks]['keywords'];
                    $modules[$k]['config'] = $kw[$ks]['config'];
                    $modules[$k]['enable'] = $kw[$ks]['enable'];
                }
            }
        }
        $this->assign('modules', $modules);
        $this->display();
    }

    /**
     * 功能变量安装/编辑
     */
    public function edit()
    {
        //echo $hanlder;exit;
        if (IS_POST) {
            $handler = I('post.handler');
            $cfg_value = I('post.cfg_value');
            $data = I('post.data');
            if (empty($data['keywords'])) {
                $this->message('请填写扩展词', NULL, 'error');
            }

            $data['config'] = serialize($cfg_value);
            $data['type'] = 'function';
            $data['wechat_id'] = session('wechat_id');
            // 数据库是否存在该数据
            $rs = $this->model->table('wechat_extend')
                ->field('name, config, enable')
                ->where('command = "' . $data['command'] . '" and wechat_id = ' . session('wechat_id'))
                ->find();
            if (! empty($rs)) {
                // 已安装
                if ($rs['enable'] == 1 && empty($handler)) {
                    $this->message('插件已安装', NULL, 'error');
                } else {
                    $data['enable'] = 1;
                    $this->model->table('wechat_extend')
                        ->data($data)
                        ->where('command = "' . $data['command'] . '" and wechat_id = ' . session('wechat_id'))
                        ->update();
                }
            } else {
                $data['enable'] = 1;
                $this->model->table('wechat_extend')
                    ->data($data)
                    ->insert();
            }
            $this->message('安装编辑成功', url('index'));
        }
        $handler = I('get.handler');
        // 编辑操作
        if (! empty($handler)) {
            // 获取配置信息
            $info = $this->model->table('wechat_extend')
                ->field('name, keywords, command, config, enable')
                ->where('command = "' . $this->plugin_name . '" and wechat_id = ' . session('wechat_id'))
                ->find();
            // 修改页面显示
            if (empty($info)) {
                $this->message('请选择要编辑的功能扩展', NULL, 'error');
            }
            $info['config'] = unserialize($info['config']);
        }

        // 插件文件
        $file = ADDONS_PATH . $this->plugin_type . '/' . $this->plugin_name . '/' . $this->plugin_name . '.class.php';
        // 插件配置
        $config_file = ADDONS_PATH . $this->plugin_type . '/' . $this->plugin_name . '/config.php';
        if (file_exists($file)) {
            require_once ($file);
            //编辑
            if(!empty($info['config'])){
                $config = $info;
                $config['handler'] = 'edit';
            }
            else{
                $config = require_once ($config_file);
            }
            
            if (! is_array($config)) {
                $config = array();
            }
            $obj = new $this->plugin_name($config);
            $obj->install();
            $obj->action();
            
        }
    }


    /**
     * 功能变量卸载
     */
    public function uninstall()
    {
        $keywords = I('get.ks');
        if (empty($keywords)) {
            $this->message('请选择要卸载的功能扩展', NULL, 'error');
        }
        $config = $this->model->table('wechat_extend')
            ->field('enable')
            ->where('command = "' . $keywords . '" and wechat_id = ' . session('wechat_id'))
            ->getOne();
        $data['enable'] = 0;
        // 序列化
        $this->model->table('wechat_extend')
            ->data($data)
            ->where('command = "' . $keywords . '" and wechat_id = ' . session('wechat_id'))
            ->update();
        
        $this->message('卸载成功', url('index'));
    }

    /**
     * 授权OAuth
     */
    public function oauth()
    {
        $list = $this->model->table('wechat_extend')
            ->field('name, config')
            ->where('type = "oauth" and enable = 1')
            ->find();
        if ($list) {
            $list['config'] = unserialize($list['config']);
        }
        
        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 授权OAuth编辑
     */
    public function oauth_edit()
    {
        if (IS_POST) {
            $data['name'] = I('post.name');
            $config = I('post.data');
            if (empty($data['name'])) {
                $this->message('请填写规则名称', NULL, 'error');
            }
            if (empty($config['redirect_uri'])) {
                $this->message('请填写回调地址', NULL, 'error');
            }
            $data['config'] = serialize($config);
            $this->model->table('wechat_extend')
                ->data($data)
                ->where('type = "oauth"')
                ->update();
            
            $this->message('编辑成功', url('oauth'));
        }
        $rs = $this->model->table('wechat_extend')
            ->field('name, config')
            ->where('type = "oauth"')
            ->find();
        if ($rs) {
            $rs['config'] = unserialize($rs['config']);
        }
        
        $this->assign('rs', $rs);
        $this->display();
    }

    /**
     * 获取微信插件及配置
     *
     * @return multitype:
     */
    private function read_wechat()
    {
        $modules = glob(ROOT_PATH . 'plugins/wechat/*/config.php');
        foreach ($modules as $file) {
            $config[] = require_once ($file);
        }
        return $config;
    }
}
