<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：AuhtorizationControoller.class.php
 * ----------------------------------------------------------------------------
 * 功能描述：众筹控制器
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */
/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

class CrowdController extends AdminController {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->assign('ur_here', L('crowd'));
        $this->assign('action', ACTION_NAME);
    }
    /**
     * 众筹回报项目列表页
     */
    public function plan_list() {
        $goods_id = I('get.goods_id');
        $this->assign('goods_id', $goods_id);
        //分页
        $filter['page'] = '{page}';
        $offset = $this->pageLimit(url('plan_list', $filter), 10);
        $total = $this->model->table('crowd_plan')
                ->where(array('goods_id' => $goods_id))
                ->order('sort_order desc')
                ->count();
        $this->assign('page', $this->pageShow($total));
        $sql = 'select cp_id,name,goods_id,shop_price,number,sort_order,backey_num,sort_order,status from ' . $this->model->pre . 'crowd_plan where goods_id=' . $goods_id . ' order by cp_id desc limit ' . $offset;
        $plan_list = $this->model->query($sql);
        $this->assign('plan_list', $plan_list);
        $this->display();
    }

    /**
     * 增加和修改众筹项目
     */
    public function add_plan() {
        $goods_id = I('get.goods_id');
        $this->assign('goods_id', $goods_id);
        if (IS_POST) {
            $data = I('post.data');
            // 商品图片处理
            if ($_FILES['cp_img']['name']) {
                $result = $this->ectouchUpload('cp_img', 'crowd_plan');
                if ($result['error'] > 0) {
                    $this->message($result['message'], NULL, 'error');
                }
                $data['cp_img'] = substr($result['message']['cp_img']['savepath'], 2) . $result['message']['cp_img']['savename'];
            }
            if (empty($data['cp_id'])) {
                //入库
                $this->model->table('crowd_plan')
                        ->data($data)
                        ->insert();
            } else {
                //修改
                $this->model->table('crowd_plan')
                        ->data($data)
                        ->where(array('cp_id' => $data['cp_id']))
                        ->update();
            }
            $this->redirect(url('crowd/plan_list', array('goods_id' => $data['goods_id'])));
        }
        if (I('cp_id')) {
            $cp_id = I('cp_id', '', 'intval');
            $plans = $this->model->table('crowd_plan')->field()->where(array('cp_id' => $cp_id))->find();
            $this->assign('plans', $plans);
        }
        $this->display();
    }

    /**
     * 删除众筹方案
     */
    public function del_plan() {
        $id = I('get.cp_id');
        if (empty($id)) {
            $this->message(L('menu_select_del'), NULL, 'error');
        }
        $this->model->table('crowd_goods')
                ->where(array('goods_id' => $id))
                ->delete();
        $this->message(L('drop') . L('success'), url('category_list'));
    }

    /**
     * 添加众筹商品
     */
    public function goods_add() {
        if (IS_POST) {
            $data = I('post.data');
            // 商品图片处理
            if (!empty($_FILES['goods_img']['name'])) {
                $result = $this->ectouchUpload('goods_img', 'crowd');
                if ($result['error'] > 0) {
                    $this->message($result['message'], NULL, 'error');
                }
                $data['goods_img'] = substr($result['message']['goods_img']['savepath'], 2) . $result['message']['goods_img']['savename'];
            }
            //商品相册  
            $uploadfile;
            if (!empty($_FILES['gallery_img']['name'])) {
                $dest_folder = 'data/attached/crowd/thumb/';   //上传图片保存的路径 图片放在跟你upload.php同级的picture文件夹里
                $arr = array();  //定义一个数组存放上传图片的名称方便你以后会用的，如果不用那就不写
                $count = 0;
                if (!file_exists($dest_folder)) {
                    mkdir($dest_folder, 0777);
                }
                $file=array();
                foreach ($_FILES["gallery_img"]["error"] as $key => $error) {
                    if ($error == 0) {
                        $tmp_name = $_FILES["gallery_img"]["tmp_name"][$key];
                        $name = $_FILES["gallery_img"]["name"][$key];
                        $uploadfile = $dest_folder . $name;
                        move_uploaded_file($tmp_name, $uploadfile);
                        $arr[$count] = $uploadfile;
                        $file[] = $uploadfile;
                        $count++;
                    }
                }
                $data['gallery_img'] = implode(',', $file);
            }
            $data['start_time'] = time($data['start_time']);
            $data['end_time'] = time($data['end_time']);
            if (empty($data['goods_id'])) {
                //入库
                $this->model->table('crowd_goods')
                        ->data($data)
                        ->insert();
            } else {
                //修改
                $this->model->table('crowd_goods')
                        ->data($data)
                        ->where(array('goods_id' => $data['goods_id']))
                        ->update();
            }
            $this->redirect(url('crowd/index'));
        }
        if (I('goods_id')) {
            $goods_id = I('goods_id', '', 'intval');
            $goods_info = $this->model->table('crowd_goods')->field()->where(array('goods_id' => $goods_id))->find();
            $data['start_time'] = time($data['start_time']);
            $data['end_time'] = time($data['end_time']);
            $this->assign('goods', $goods_info);
        }
        $this->assign('cat_select', cat_lists(0, 0, true));
        $this->display();
    }

    /**
     * 众筹商品列表
     */
    public function index() {
        //分页
        $filter['page'] = '{page}';
        $offset = $this->pageLimit(url('index', $filter), 10);
        $total = $this->model->table('crowd_goods')
                ->order('sort_order desc')
                ->count();
        $this->assign('page', $this->pageShow($total));
        $sql = 'select goods_id,status,sum_price,total_price,shiping_time,goods_name,cat_id,sort_order,start_time,end_time from ' . $this->model->pre . 'crowd_goods order by sort_order desc limit ' . $offset;
        $goods_list = $this->model->query($sql);
        foreach ($goods_list as $key => $value) {
            $goods_list[$key]['start_time'] = date('Y-m-d H:i:s', $value['start_time']);
            $goods_list[$key]['end_time'] = date('Y-m-d H:i:s', $value['end_time']);
        }
        $this->assign('goods', $goods_list);
        $this->assign('cat_select', cat_lists(0, 0, false));
        $this->display();
    }

    /**
     * 删除众筹商品
     */
    public function del_goods() {
        $id = I('get.goods_id');
        if (empty($id)) {
            $this->message(L('menu_select_del'), NULL, 'error');
        }
        $this->model->table('crowd_goods')
                ->where(array('goods_id' => $id))
                ->delete();
        $this->message(L('drop') . L('success'), url('category_list'));
    }

    /**
     * 众筹订单列表
     */
    public function order_list() {
        $keywords = I('post.keywords') ? I('post.keywords') : '';
        $type= I('post.type') ? I('post.type') : '';
        $where = '1=1';
        //只搜索订单号
        if (!empty($keywords) && empty($type)) {
             $where='order_sn like "%' . $keywords . '%"'; 
         }
         //只搜索状态
        if(!empty($type) && empty($keywords)){
            if($type==0){
                 $where = '1=1';
             }
            if($type==1){
                 $where = 'pay_status !=2';
             }
            if($type==2){
                 $where = 'pay_status =2 and shipping_status=0';
             }
            if($type==3){
                 $where = 'pay_status =2 and shipping_status !=0';
             }
         }
         //两个条件都有
        if(!empty($type) && !empty($keywords)){
            if($type==1){
                $where='order_sn like "%' . $keywords . '%" and pay_status !=2 '; 
             }
            if($type==2){
                 $where='order_sn like "%' . $keywords . '%" and pay_status =2 and shipping_status=0 '; 
             }
            if($type==3){
                 $where='order_sn like "%' . $keywords . '%" and pay_status =2 and shipping_status !=0'; 
             }
          }
        //分页
        $filter['page'] = '{page}';
        $offset = $this->pageLimit(url('order_list', $filter), 15);
        $total = $this->model->table('crowd_order_info')
                ->order('add_time desc')
                ->count();
        $this->assign('page', $this->pageShow($total));
        $sql = 'select order_id,cp_id,order_sn,user_id,goods_name,order_status, shipping_status,pay_status,add_time,goods_amount from ' . $this->model->pre . 'crowd_order_info  where '.$where.' order by add_time desc limit ' . $offset;
        $order_list = $this->model->query($sql);
        $list = array();
        foreach ($order_list as $key => $value) {
            $list[$key]['order_id'] = $value['order_id'];
            $list[$key]['cp_name'] = $this->get_cp_name($value['cp_id']);
            $list[$key]['order_sn'] = $value['order_sn'];
            $list[$key]['user_id'] = $this->get_username($value['user_id']);
            $list[$key]['goods_name'] = $value['goods_name']; //名称
            $list[$key]['order_status'] = $value['order_status'];
            $list[$key]['shipping_status'] = $value['shipping_status'];
            $list[$key]['pay_status'] = $value['pay_status'];
            $list[$key]['status'] = L('os.' . $value['order_status']) . ',' . L('ps.' . $value['pay_status']) . ',' . L('ss.' . $value['shipping_status']);
            $list[$key]['goods_amount'] = $value['goods_amount']; //金额
            $list[$key]['add_time'] = date('Y-m-d H:i:s', $value['add_time']); //下单时间
        }
        $this->assign('order_list', $list);
        $this->display();
    }

    /**
     * 众筹订单详情
     */
    public function order_info() {
        $order_id = I('get.order_id', '', 'intval');
        $order_info = $this->model->table('crowd_order_info')->where(array('order_id' => $order_id))->find();
        $order_info['user_name'] = $this->get_username($order_info['user_id']);
        $order_info['status'] = L('os.' . $order_info['order_status']) . ',' . L('ps.' . $order_info['pay_status']) . ',' . L('ss.' . $order_info['shipping_status']);
        $order_info['total_fee'] = $order_info['goods_amount'] - $order_info['discount'] + $order_info['tax'] + $order_info['shipping_fee'] + $order_info['insure_fee'] + $order_info['pay_fee'] + $order_info['pack_fee'] + $order_info['card_fee'];
        $country = $this->get_region_name($order_info['country']);
        $province = $this->get_region_name($order_info['province']);
        $city = $this->get_region_name($order_info['city']);
        $order_info['address'] = $country['region_name'] . $province['region_name'] . $city['region_name'] . $order_info['address'];
        $stock = $this->get_stock($order_info['goods_id']);
        $order_info['stock'] = $stock['number'] - $stock['backey_num']; //库存
        $order_info['cp_name']=$this->get_cp_name($order_info['cp_id']);
        $this->assign('order_info', $order_info);
        $this->display();
    }
    /**
     * 发货
     */
    public function delivery() {
        if (IS_POST) {
            $data = I('post.data');
            if (isset($data['cancel'])) {
                $data['order_status'] = 3;
                $data['pay_status'] = 0;
                $data['shipping_status'] =0;
                //更新订单状态
                $this->model->table('crowd_order_info')
                        ->data(array('order_status'=>$data['order_status'],'pay_status'=>$data['pay_status'],'shipping_status'=>$data['shipping_status']))
                        ->where(array('order_id' => $data['order_id']))
                        ->update();
            } else {
                if (empty($data[invoice_no])) {
                    $this->message('请填写发货单号');
                }
                $data['order_status'] = 5;
                $data['pay_status'] = 2;
                $data['shipping_status'] = 1;
                //更新订单状态
                $this->model->table('crowd_order_info')
                        ->data($data)
                        ->where(array('order_id' => $data['order_id']))
                        ->update();
            }
            $this->redirect(url('crowd/order_list'));
        }
    }

    /**
     * 众筹分类
     */
    public function category() {
        if (IS_POST) {
            $data = array(
                'cat_id' => I('cat_id'),
                'cat_name' => I('cat_name'),
                'sort_order' => I('sort_order'),
                'cat_desc' => I('cat_desc'),
                'parent_id' => I('parent_id '),
                'is_show' => I('is_show'),
            );
            //验证数据
            $result = Check::rule(array(
                        Check::must($data['cat_name']),
                        L('must_category_name')
            ));
            if ($result !== true) {
                $this->message($result, NULL, 'error');
            }
            if (empty($data['cat_id'])) {
                // 插入数据
                $this->model->table('crowd_category')
                        ->data($data)
                        ->insert();
            } else {
                // 更新数据
                $this->model->table('crowd_category')
                        ->data($data)
                        ->where(array('cat_id' => $data['cat_id']))
                        ->update();
            }
            $this->redirect(url('crowd/category_list'));
        }
        if (I('cat_id')) {
            $cat_id = I('cat_id', '', 'intval');
            $cat_info = $this->model->table('crowd_category')->field()->where(array('cat_id' => $cat_id))->find();
            $this->assign('cat_info', $cat_info);
        }
        $this->assign('cat_select', cat_lists(0, 0, true));
        $this->display();
    }

    /**
     * 众筹分类列表
     */
    public function category_list() {
        //分页
        $filter['page'] = '{page}';
        $offset = $this->pageLimit(url('category_list', $filter), 10);
        $total = $this->model->table('crowd_category')
                ->order('sort_order desc')
                ->count();
        $this->assign('page', $this->pageShow($total));
        $sql = 'select cat_id,cat_name,cat_desc,sort_order,is_show from ' . $this->model->pre . 'crowd_category order by sort_order desc limit ' . $offset;
        $cat_list = $this->model->query($sql);
        $this->assign('cat_info', $cat_list);
        $this->assign('cat_select', cat_lists(0, 0, false));
        $this->display();
    }

    /**
     * 删除
     */
    public function del_category() {
        $id = I('get.cat_id');
        if (empty($id)) {
            $this->message(L('menu_select_del'), NULL, 'error');
        }
        $this->model->table('crowd_category')
                ->where(array('cat_id' => $id))
                ->delete();
        $this->message(L('drop') . L('success'), url('category_list'));
    }

    /**
     * 评论列表
     */
    public function message_list() {
        
    }
    
    /**
     * 查询收货地址
     */
    private function get_region_name($region_id) {
        $region_name = $this->model->table('region')->field('region_name')->where(array('region_id' => $region_id))->find();
        return $region_name;
    }

    /**
     * 获取订单的用户名称
     */
    private function get_username($user_id) {
        $username = $this->model->table('users')->field('user_name')->where(array('user_id' => $user_id))->find();
        return $username['user_name'];
    }

    /**
     * 获取订单的回报方案名称
     */
    private function get_cp_name($cp_id) {
        $name = $this->model->table('crowd_plan')->field('name')->where(array('cp_id' => $cp_id))->find();
        return $name['name'];
    }

    /**
     * 获取订单中商品的库存
     */
    private function get_stock($goods_id) {
        $stock = $this->model->table('crowd_plan')->field('number,backey_num')->where(array('goods_id' => $goods_id))->find();
        return $stock;
    }

}
