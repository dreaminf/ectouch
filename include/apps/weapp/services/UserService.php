<?php

namespace apps\weapp\services;

use \Model;
class UserService extends Model{

    /**
     * 获取用户信息
     */
    public function getUserInfo($union_id){
        $sql = 'select * from ' . $this->model->pre . 'wechat_user WHERE unionid = '.$union_id;
        $user = $this->model->getRow($sql);
        return $user;
    }
}