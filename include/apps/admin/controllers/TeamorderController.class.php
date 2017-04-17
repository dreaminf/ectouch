<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：GroupbuyControoller.class.php
 * ----------------------------------------------------------------------------
 * 功能描述：团购活动管理控制器
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */
/* 访问控制 */

class TeamorderController extends AdminController {
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->assign('ur_here', L('teamorder'));
        $this->assign('action', ACTION_NAME);
    }
    /**
     * 拼团列表
     */
    public function index() {
        $keywords = I('post.goods_name') ? I('post.goods_name') : '';
        $where = "1=1";
        if (!empty($keywords)) {
            $where= 'g.goods_name like "%' . $keywords . '%"';
        }
        //分页
        $filter['page'] = '{page}';
        $offset = $this->pageLimit(url('index', $filter), 20);
        $sql = 'select count(t.team_id) as max from ' . $this->model->pre . 'team_log as t left join ' . $this->model->pre . 'goods as g on t.goods_id=g.goods_id where ' . $where . ' and t.is_show=1 order by t.start_time desc  ' ;
        $max = $this->model->query($sql);
        foreach ($max as $key => $value){
            $total=$value['max'];
        }
        $this->assign('page', $this->pageShow($total));
        $sql = 'select t.*,g.goods_name,g.validity_time,g.team_num from ' . $this->model->pre . 'team_log as t left join ' . $this->model->pre . 'goods as g on t.goods_id=g.goods_id where ' . $where . ' and t.is_show=1 order by t.start_time desc limit ' . $offset;
        $log_list = $this->model->query($sql);
        $time = gmtime();
        foreach ($log_list as $key => $value) {
            $log_list[$key]['start_time'] = date('Y-m-d H:m:s', $value['start_time']);
            $endtime = $value['start_time'] + $value['validity_time'] * 3600;
            $cle =$endtime-$time; //得出时间戳差值
            $d = floor($cle/3600/24);
            $h = floor(($cle%(3600*24))/3600);
            $m = floor(($cle%(3600*24))/600/60);
            $log_list[$key]['time']=$d.'天'.$h.'小时'.$m.'分钟';
            $log_list[$key]['cle']=$cle;
            $number=$this->surplus_num($value['team_id']);
            $log_list[$key]['surplus_num']=$value['team_num']-$number;
        }
        $this->assign('log_list', $log_list);
        $this->display();
    }
    
    //对应团的订单
    public function detile() {
        $team_id = I('team_id');
        $this->assign('team_id', $team_id);
        $team_id = I('team_id');
        $keywords = I('post.order_sn') ? I('post.order_sn') : '';
        $filter['page'] = '{page}';
        $offset = $this->pageLimit(url('detile', $filter), 20);
        $total = $this->model->table('order_info')
                ->order('add_time desc')
                ->count();
        $this->assign('page', $this->pageShow($total));
        if(!empty($keywords)){
           $where= 'order_sn like "%' . $keywords . '%" and team_id=' . $team_id . ' and extension_code="team_buy" ';
           $sql='select * from {pre}order_info where '.$where.'order by add_time desc limit '.$offset;
     
           $res=$this->model->query($sql);
        }else{
            $res = $this->model->table('order_info')->where(array('team_id' => $team_id, 'extension_code' => 'team_buy'))->LIMIT($offset)->select();
            
        }
        foreach ($res as $key => $value) {
            $res[$key]['order_id'] = $value['order_id'];
            $res[$key]['order_sn'] = $value['order_sn'];
            $res[$key]['user_id'] = $this->get_username($value['user_id']);
            $res[$key]['goods_name'] = $value['goods_name']; //名称
            $res[$key]['order_status'] = $value['order_status'];
            $res[$key]['shipping_status'] = $value['shipping_status'];
            $res[$key]['pay_status'] = $value['pay_status'];
            $res[$key]['status'] = L('os.' . $value['order_status']) . ',' . L('ps.' . $value['pay_status']) . ',' . L('ss.' . $value['shipping_status']);
            $res[$key]['goods_amount'] = $value['goods_amount']; //金额
            $res[$key]['add_time'] = date('Y-m-d H:i:s', $value['add_time']); //下单时间
        }
        $this->assign('order_list', $res);

        $this->display();
    }

    //删除
    public function del() {
        $id = I('team_id');
        if (empty($id)) {
            $this->message(L('menu_select_del'), NULL, 'error');
        }
          $this->model->table('team_log')
                        ->data(array('is_show'=>0))
                        ->where(array('team_id' => $id))
                        ->update();
        $this->message(L('drop') . L('success'), url('index'));
    }
    
    /**
     * 计算该拼团已参与人数
     */
    public function surplus_num($team_id = 0) {
      $res=$this->model->table('order_info')->where(array('team_id' =>$team_id,'extension_code'=>'team_buy','pay_status'=>PS_PAYED))->count();
      return $res;
    }
     /**
     * 获取订单的用户名称
     */
    private function get_username($user_id) {
        $username = $this->model->table('users')->field('user_name')->where(array('user_id' => $user_id))->find();
        return $username['user_name'];
    }

}
