<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Respond extends IndexController
{

    private $code = null;
    private $type = null;

    public function __construct()
    {
        parent::__construct();
        $this->code = I('get.code');
        $this->type = I('get.type');
	}

    // 发送
    public function index()
    {
        /* 判断是否启用 */
        $condition['pay_code'] = $this->code;
        $condition['enabled'] = 1;
        $enabled = $this->model->table('touch_payment')->where($condition)->count();
        if ($enabled == 0) {
            $msg = L('pay_disabled');
        } else {
            $plugin_file = ADDONS_PATH.'payment/' . $this->code . '.php';
            /* 检查插件文件是否存在，如果存在则验证支付是否成功，否则则返回失败信息 */
            if (file_exists($plugin_file)) {
                /* 根据支付方式代码创建支付类的对象并调用其响应操作方法 */
                include_once($plugin_file);
                $payobj = new $this->code();
                /* 处理异步请求 */
                if($this->type == 'notify'){
                    @$payobj->notify($this->data);
                }
                $msg = (@$payobj->callback($this->data)) ? L('pay_success') : L('pay_fail');
            } else {
                $msg = L('pay_not_exist');
            }
        }
        //显示页面
        $this->assign('message', $msg);
        // $this->display('respond.dwt');
    }
}