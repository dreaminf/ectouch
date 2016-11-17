<?php

class UserController extends PubController{

    private $user_id;
    public function __construct()
    {
        parent::__construct();

        $union_id = $this->globalInfo['union_id'];
        $userInfo = $this->common->getServer('user')->getUserInfo($union_id);
        $this->user_id = $userInfo['uid'];
        require_once BASE_PATH.'/config/constant.php';
    }

    /**
     * 用户中心
     */
    public function index(){
        $array = array();
        if ($rank = model('ClipsBase')->get_rank_info()) {
            $this->assign('rank_name', sprintf(L('your_level'), $rank['rank_name']));
        }
        // 待付款
        $array['not_pays'] = model('ClipsBase')->not_pay($this->user_id);

        // 待收货
        $array['not_shouhuos'] = model('ClipsBase')->not_shouhuo($this->user_id);
        // 红包
        $array['bonus'] = model('ClipsBase')->my_bonus($this->user_id);
        // 待评价
        $array['not_comment'] = model('ClipsBase')->not_pingjia($this->user_id);

        // 用户积分余额
        $user_pay = model('ClipsBase')->pay_money($this->user_id);
        $array['user_money'] = $user_pay['user_money'];  //余额
        $array['user_points'] = $user_pay['pay_points'];	//积分
        // 获取未读取消息数量
        $array['msg_list'] = model('ClipsBase')->msg_lists($this->user_id);
        // 收藏数量
        $array['goods_num'] = model('ClipsBase')->num_collection_goods($this->user_id);
        // 收藏
        $array['goods_list'] = model('ClipsBase')->get_collection_goods($this->user_id, 5, 0);
        // 评论
        $array['comment_list'] = model('ClipsBase')->get_comment_list($this->user_id, 5, 0);

        $this->common->responseAct($array);

    }
}