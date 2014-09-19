<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：spiderControoller.class.php
 * ----------------------------------------------------------------------------
 * 功能描述：采集数据控制器类
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */
/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

class SpiderController extends CommonController {

    private $categoryUrl = 'http://m.tmall.com/tmallCate.htm';

    public function __construct() {
        parent::__construct();
    }

    /**
     * 采集首页
     */
    public function index() {

        echo '1、<a href="' . url('category') . '">采集天猫分类</a><br />';
        echo '2、<a href="' . url('goods_list') . '">生成商品列表</a><br />';
        echo '3、<a href="' . url('goods') . '">采集天猫商品</a>';
    }

    public function category() {
        //采集数据
        $content = Http::doGet($this->categoryUrl);
        //一级分类
        preg_match_all("/<div class=\"crow_icon\">(.*?)src=\"(.*?)\"(.*?)<span>(.*?)<\/span>(.*?)level2\">(.*?)<\/div>/is", $content, $cat_list);
        if (is_array($cat_list[0])) {
            $category_list = array();
            foreach ($cat_list[0] as $k => $vo) {
                $category_list[$cat_list[6][$k]] = array('name' => $cat_list[4][$k], 'img' => $cat_list[2][$k]);
            }
        }
        //全部分类（JSON格式）
        preg_match("/\"level3\": (.*?)}]}/is", $content, $match);
        $result = json_decode(trim($match[1] . '}]}'), true);
        //初始化变量
        $data = array(
            'parent_id' => 0,
            'sort_order' => 0,
            'keywords' => '',
            'cat_desc' => '',
            'measure_unit' => '',
            'cat_name' => '',
            'show_in_nav' => 0,
            'style' => '',
            'is_show' => 1,
            'grade' => 0,
            'filter_attr' => 0
        );
        //分类链接
        $cate_url = array();
        //一级分类
        if (is_array($category_list)) {
            foreach ($category_list as $key => $cat) {
                // 是否存在
                $condition['parent_id'] = 0;
                $condition['cat_name'] = $cat['name'];
                $cat_info = $this->model->table('category')->where($condition)->find();
                if (empty($cat_info)) {
                    // 插入数据
                    $data['parent_id'] = 0;
                    $data['cat_name'] = $cat['name'];
                    $cat_id = $this->model->table('category')->data($data)->insert();
                    // 分类附表
                    $cat['img'] = $this->getImage($cat['img'], $cat_id, ROOT_PATH . '/data/attached/cat_image/', 1);

                    $data2['cat_id'] = $cat_id;
                    $data2['cat_image'] = $cat['img'];
                    $this->model->table('touch_category')->data($data2)->insert();
                    echo '分类：' . $key . '插入成功.<br>';
                } else {
                    $cat_id = $cat_info['cat_id'];
                }
                //二级分类
                if (is_array($result[$key])) {
                    foreach ($result[$key] as $vo) {
                        // 是否存在
                        $condition['parent_id'] = $cat_id;
                        $condition['cat_name'] = $vo['n'];
                        $child_cat = $this->model->table('category')->where($condition)->find();
                        if (empty($child_cat)) {
                            // 插入数据
                            $data['parent_id'] = $cat_id;
                            $data['cat_name'] = $vo['n'];

                            $child_cat_id = $this->model->table('category')->data($data)->insert();
                            // 分类附表
                            $data2['cat_id'] = $child_cat_id;
                            $data2['cat_image'] = '';
                            $this->model->table('touch_category')->data($data2)->insert();
                        }
                        //生成分类链接
                        $cate_url[] = array('cat_id' => $child_cat_id, 'p' => $vo['p']);
                    }
                }
            }
        }
        file_put_contents('./data/tmall_cate.dat', serialize($cate_url));
        //调整链接
        $this->redirect(url('index'));
    }

    protected function getImage($url, $filename = '', $dirName, $type = 0) {
        if ($url == '') {
            return false;
        }
        //获取文件类型
        $suffix = substr(strrchr($url, '.'), 1);

        //设置保存后的文件名
        $filename = $filename . '.' . $suffix;

        //获取远程文件资源
        if ($type) {
            $ch = curl_init();
            $timeout = 5;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $file = curl_exec($ch);
            curl_close($ch);
        } else {
            ob_start();
            readfile($url);
            $file = ob_get_contents();
            ob_end_clean();
        }
        //设置文件保存路径
        if (!file_exists($dirName)) {
            mkdir($dirName, 0777, true);
        }
        //保存文件
        $res = fopen($dirName . $filename, 'a');
        fwrite($res, $file);
        fclose($res);
        return 'data/attached/cat_image/' . $filename;
        //return "{'fileName':$filename, 'saveDir':$dirName}";
    }

    //采集商品列表
    public function goods_list() {
        $page = I('page', 0);
        $count = I('count', 0);
        $tmall_cate = file_get_contents('./data/tmall_cate.dat');
        $tmall_cate = unserialize($tmall_cate);
        if ($count <= 0) {
            $count = count($tmall_cate);
        }

        $next_url = url('goods_list', array('count' => $count, 'page' => $page + 1));
        $list_url = 'http://s.m.tmall.com/search_data.htm?p=1&';

        $content = Http::doGet($list_url . $tmall_cate[$page]['p']);
        $result = json_decode($content, true);
        if (is_array($result['listItem'])) {
            $data = array();
            foreach ($result['listItem'] as $vo) {
                $data['cat_id'] = $tmall_cate[$page]['cat_id'];
                $data['goods_name'] = $vo['title_list'];
                $data['price'] = $vo['price'];
                $data['original_price'] = $vo['original_price'];
                $data['img'] = $vo['img'];
                $data['url'] = $vo['url'];
                $data['sold'] = $vo['sold'];
                $data2['id'] = $vo['item_id'];
                $data2['data'] = serialize($data);
                //是否存在
                $condition['id'] = $vo['item_id'];
                $goods = $this->model->table('touch_spider')->where($condition)->find();
                if (empty($goods)) {
                    $this->model->table('touch_spider')->data($data2)->insert();
                    dump($data);
                }
                unset($data);
            }
        }
        if ($page + 1 >= $count) {
            echo '<meta http-equiv="refresh" content="1;url=' . url('index') . '">';
        } else {
            echo '<meta http-equiv="refresh" content="0.1;url=' . $next_url . '">';
        }
    }

    //采集商品详情链接
    public function goods() {
        $page = I('page', 0);
        $count = I('count', 0);
        if ($count <= 0) {
            $result = $this->model->table('touch_spider')->field('id')->select();
            file_put_contents('./data/tmall_list.dat', serialize($result));
        }
        $tmall_list = unserialize(file_get_contents('./data/tmall_list.dat'));
        $count = count($tmall_list);

        $next_url = url('goods', array('count' => $count, 'page' => $page + 1));

        $condition['id'] = $tmall_list[$page]['id'];
        $goods = $this->model->table('touch_spider')->where($condition)->find();
        $content = unserialize($goods['data']);


        $data['url'] = $this->get_desc_url($content['url']);
        $this->model->table('touch_spider')->data($data)->where($condition)->update();

        dump($data);
        if ($page + 1 >= $count) {
            echo '<meta http-equiv="refresh" content="1;url=' . url('index') . '">';
        } else {
            echo '<meta http-equiv="refresh" content="0.1;url=' . $next_url . '">';
        }
    }

    //采集商品详情
    public function content() {
        $page = I('page', 0);
        $tmall_list = unserialize(file_get_contents('./data/tmall_list.dat'));

        $condition['id'] = $tmall_list[$page]['id'];
        $goods = $this->model->table('touch_spider')->where($condition)->find();
        $content = unserialize($goods['data']);
        $content['content'] = Http::doGet($goods['url']);
        $text = iconv("GBK", "UTF-8", mb_substr($content['content'], 10, -3));

        echo '<cat_id>' . $content['cat_id'] . '</cat_id><goods_name>' . $content['goods_name'] . '</goods_name><price>' . $content['price'] . '</price><original_price>' . $content['original_price'] . '</original_price><img>' . $content['img'] . '</img><url>' . $content['url'] . '</url><sold>' . $content['sold'] . '</sold><content>' . $text . '</content>';
    }

    protected function get_desc_url($url = '') {
        $content = Http::doGet($url);
        preg_match("/\"descUrl\":\"(.*?)\"/is", $content, $result); //匹配此页面的标题
        return $result[1];
    }

}
