<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：IndexController.class.php
 * ----------------------------------------------------------------------------
 * 功能描述：ECTouch众筹分类商品列表控制器
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */
/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

class CrowdfundingController extends CommonController {
	 /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
		$this->user_id = $_SESSION['user_id'];
        $this->cat_id = I('request.id');
		$this->type = I('request.type');
		$this->keywords = I('request.keywords');
		$this->goods_id = I('request.id');
		$this->size = 10;
		$this->page = 1;

		if (!empty($_COOKIE['ZCECS']['keywords'])) {
            $histroy = explode(',',$_COOKIE['ZCECS']['keywords']);
            foreach ($histroy as $key=>$val) {
                if($key < 10){
                    $zchistroy_list[$key] = $val;
                }
            }
            $this->assign('zcsearch_histroy', $zchistroy_list);
        }


    }

    /**
     * 众筹项目列表信息
     */
    public function index() {
        $category = model('Crowdfunding')->category_all($this->type);//获取众筹分类
		$goodslist = $this->crowd_goods();//获取众筹产品列表
		if($this->cat_id){
			$this->assign('id', $this->cat_id);
		}

		$this->assign('type', $this->type);
		$this->assign('page', $this->page);
        $this->assign('size', $this->size);
		$this->assign('keywords', $this->keywords);
		$this->assign('goods_list', $goodslist);
		$this->assign('category', $category);
        $this->display('crowd/crowd_category.html');
    }

    /**
     * 众筹项目列表信息
     */
	public function crowd_goods() {

		/*记录搜索历史记录*/
		if (!empty($_COOKIE['ZCECS']['keywords'])) {
			$history = explode(',', $_COOKIE['ZCECS']['keywords']);
			array_unshift($history, $this->keywords); //在数组开头插入一个或多个元素
			$history = array_unique($history);  //移除数组中的重复的值，并返回结果数组。
			setcookie('ZCECS[keywords]', implode(',', $history), gmtime() + 3600 * 24 * 30);
		} else {
			setcookie('ZCECS[keywords]', $this->keywords, gmtime() + 3600 * 24 * 30);
		}



		if ($this->keywords) {
            $where .= " and goods_name like '%$this->keywords%' ";
        }
		if ($this->cat_id > 0) {
            $where .= " and cat_id = $this->cat_id ";
        }

		if ($this->type) {
            switch ($this->type) {
                case 'new':
                    $where .= ' order by start_time DESC';
                    break;
                case 'sum_price':
                    $where .= ' order by sum_price DESC ';
                    break;
                case 'buy_num':
                    $where .= ' order by buy_num DESC ';
                    break;
				case 'end':
                    $where .= ' order by end_time asc ';
                    break;
                default:
                    $where .= '';
            }
        }
		$start = ($this->page - 1) * $this->size;
		$now = time();
		$sql = 'SELECT goods_id, cat_id, goods_name, goods_img, sum_price, start_time,end_time,status '.'FROM '
		. $this->model->pre . 'crowd_goods ' . "WHERE start_time <= '$now' AND end_time >= '$now' and status < 2 $where LIMIT $start , $this->size";
		//echo $sql;
        $res = $this->model->query($sql);
		$goods = array();
        foreach ($res AS $key => $row) {
            $goods[$key]['id'] = $row['goods_id'];
			$goods[$key]['cat_id'] = $row['cat_id'];
            $goods[$key]['goods_name'] = $row['goods_name'];
            $goods[$key]['buy_num'] = model('Crowdfunding')->crowd_buy_num($row['goods_id']);
			$goods[$key]['start_time'] = model('Crowdfunding')->start_time_past($row['start_time'],time());
            //$goods[$key]['sum_price'] = price_format($row['sum_price']);
			$goods[$key]['sum_price'] = $row['sum_price'];
            $goods[$key]['total_price'] = model('Crowdfunding')->crowd_buy_price($row['goods_id']);
            $goods[$key]['goods_img'] = $row['goods_img'];
			$goods[$key]['url'] = url('Crowdfunding/goods_info', array('id' => $row['goods_id']));
			if($row['sum_price'] > 0){
				$goods[$key]['bar'] = $goods[$key]['total_price']*100/$row['sum_price'];
			}
			$goods[$key]['bar'] = round($goods[$key]['bar'],1); //计算百分比
        }
        return $goods;

	}
	/**
     * 异步加载商品列表
     */
    public function async_list()
    {

		$this->page = I('request.page') > 0 ? intval(I('request.page')) : 1;
        $this->type = I('request.type');
		$this->cat_id = I('request.id');
		$this->keywords = I('request.keywords');

        $goodslist = $this->crowd_goods();
        die(json_encode(array('list' => $goodslist)));
        exit();
    }

	/**
     * 众筹项目详情信息
     */
	public function goods_info() {

		$goods = model('Crowdfunding')->crowd_goods_info($this->goods_id);  //项目信息
		if($goods == false){
			ecs_header("Location: " . url('crowdfunding/index') . "\n");
		}
		//项目相册
		$gallery = explode(',',$goods['gallery_img']);
		foreach ($gallery as $key=>$val) {
			$gallery_img[$key] = $val;
        }
		if($gallery_img[0] != ''){
			$this->assign('gallery_img', $gallery_img);
		}

		$goods_plan = model('Crowdfunding')->crowd_goods_paln($this->goods_id);//项目方案
		$comment_list = model('Crowdfunding')->crowd_comment($this->goods_id);//项目评论
		$trends_list = model('Crowdfunding')->crowd_trends($this->goods_id);//项目动态
		$buy_list = model('Crowdfunding')->crowd_buy($this->goods_id);//项目的支持者
		// 检查是否已经存在于用户的关注列表
        if ($_SESSION ['user_id']) {
            $where['user_id'] = $_SESSION ['user_id'];
            $where['goods_id'] = $this->goods_id;
            $rs = $this->model->table('crowd_like')->where($where)->count();
            if ($rs > 0) {
                $this->assign('sc', 1);
            }
        }
		$this->assign('goods', $goods);
		$this->assign('id', $this->goods_id);
		$this->assign('goods_plan', $goods_plan);
		$this->assign('comment_list', $comment_list);
		$this->assign('trends_list', $trends_list);
		$this->assign('buy_list', $buy_list);
		$this->display('crowd/crowd_goods.html');
	}

	/**
     * 改变方案数量修改价格
     */
	public function plan_price() {

		//格式化返回数组
        $res = array(
            'err_msg' => '',
            'result' => '',
            'qty' => 1
        );
        // 获取参数
		$goods_id = (isset($_REQUEST ['goods_id'])) ? intval($_REQUEST ['goods_id']) : 1;
		$cp_id = (isset($_REQUEST ['cp_id'])) ? intval($_REQUEST ['cp_id']) : 1;
        $number = (isset($_REQUEST ['number'])) ? intval($_REQUEST ['number']) : 1;
        // 如果商品id错误
        if ($goods_id == 0) {
            $res ['err_msg'] = L('err_change_attr');
            $res ['err_no'] = 1;
        } else {
            // 查询
            $condition['goods_id'] = $goods_id;
			$condition['cp_id'] = $cp_id;
            $goods = $this->model->table('crowd_plan')->field('shop_price , number ,backey_num')->where($condition)->find();
			$surplus_num = $goods['number'] - $goods['backey_num'];
            if ($number <= 0) {
                $res ['qty'] = $number = 1;
            } else {
                $res ['qty'] = $number;
            }
			if($number > $surplus_num){
				$res ['err_msg'] = '已超出计划销售数量';
				$res ['number'] = $surplus_num;
				$res ['result'] = price_format($goods['shop_price'] * $surplus_num);
				$res ['err_no'] = 1;
			}else{
				$res ['result'] = price_format($goods['shop_price'] * $number);
			}  
        }
        die(json_encode($res));
	}

	/**
     * 加入关注
     */
	public function add_crowd_like(){

		$result = array(
            'error' => 0,
            'message' => ''
        );

		if ($this->user_id == 0) {
            $result['error'] = 2;
            $result['message'] = '请先登录';
            die(json_encode($result));
        }

        $goods_id = intval($_GET['id']);
		// 检查是否已经存在于用户的关注列表
		$where['user_id'] = $this->user_id;
		$where['goods_id'] = $goods_id;
		$rs = $this->model->table('crowd_like')
				->where($where)
				->count();
		if ($rs > 0) {
			$rs = $this->model->table('crowd_like')
					->where($where)
					->delete();
			if (!$rs) {
				$result['error'] = 1;
				$result['message'] = M()->errorMsg();
				die(json_encode($result));
			} else {
				$result['error'] = 0;
				$result['message'] = '已成移除加关注列表';
				die(json_encode($result));
			}
		} else {
			$data['user_id'] = $this->user_id;
			$data['goods_id'] = $goods_id;
			$data['add_time'] = time();
			if ($this->model->table('crowd_like')
							->data($data)
							->insert() === false) {
				$result['error'] = 1;
				$result['message'] = M()->errorMsg();
				die(json_encode($result));
			} else {
				$result['error'] = 0;
				$result['message'] = '已成功添加关注列表';
				die(json_encode($result));
			}
		}


	}

	/**
     * 获取众筹项目的评论列表
     */
	public function crowd_comment_list(){
		$cmt = new stdClass();
		if(!empty($_GET['id'])){
			$cmt->id = !empty($_GET['id']) ? intval($_GET['id']) : 0;
			$_SESSION['goods_id']= $cmt->id;
		}else{
			$_SESSION['goods_id'];
		}

        $cmt->type = !empty($_GET['type']) ? intval($_GET['type']) : 1;
        $cmt->page = isset($_GET['page']) && intval($_GET['page']) > 0 ? intval($_GET['page']) : 1;
        $com = model('Crowdfunding')->crowd_comment_info($_SESSION['goods_id']);
        $this->assign('comments_info', $com);
        $pay = 0;
        $size = I(C('page_size'), 10);
        $this->assign('show_asynclist', C('show_asynclist'));
        $count = $com['sum_count'];
        $filter['page'] = '{page}';
        $offset = $this->pageLimit(url('Crowdfunding/crowd_comment_list', $filter), $size);
        $offset_page = explode(',', $offset);
        $comment_list = model('Crowdfunding')->crowd_get_comment($_SESSION['goods_id'], $pay, $offset_page[1], $offset_page[0]);
        $this->assign('comment_list', $comment_list);

        $result['message'] = C('comment_check') ? L('cmt_submit_wait') : L('cmt_submit_done');
        $this->assign('id', $cmt->id);
        //$this->assign('type', $cmt->type);
        $this->assign('pager', $this->pageShow($count));
        $this->assign('title', L('goods_comment'));

		$this->display('crowd/crowd_comment_list.html');
	}


	/**
     * 获取众筹项目的详情
     */
	public function crowd_goods_properties(){
		$goods_desc = $this->model->table('crowd_goods')->where(array('goods_id'=>$this->goods_id))->field('goods_desc,goods_id')->find();
		$this->assign('goods', $goods_desc);
		$this->assign('id', $this->goods_id);
		$this->display('crowd/crowd_goods_info.html');
	}



	 /**
     * 清空浏览历史
     */
    public function clear_history() {
        // ajax请求
        if (IS_AJAX && IS_AJAX) {
            setcookie('ZCECS[keywords]', '', 1);
            echo json_encode(array('status' => 1));
        } else {
            echo json_encode(array('status' => 0));
        }
    }













}
