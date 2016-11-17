<?php

namespace apps\weapp\services;

class CommonService {
    private $request;
    public function __construct($data = null)
    {
        $this->request = I('');
    }

    //获取参数
    public function get($name = null){
        $request = $this->request;

        if($name == null){
            return $request;
        }

        return $request[$name];
    }
    /**
     * 获取函数
     * $name 服务名
     * $paras 参数
     */
    public function getServer($name, $paras = array()){
        static $serverContainer = array();

        if($serverContainer[$name]){
            return $serverContainer[$name];
        }

        $serverName = ucfirst($name) . 'Service';
        $newclass = '\apps\weapp\services\\'.$serverName;
        //加载文件
        require_once APP_PATH.APP_NAME.'/services/'.$serverName.'.php';

        if(class_exists($newclass)){
            $serverContainer[$name] = new $newclass($paras);
            return $serverContainer[$name];
        }else{
            throw new \Exception('can not find this service');
        }
    }

    /**
     * 响应代码
     */
    public function responseAct($array){
        header('Content-type: application/json');
        if (is_array($array) || is_object($array)) {
            echo json_encode($array);
        }else{
            echo json_encode(array('code'=>500, 'errormsg'=>'数据类型错误'));
        }
        exit;
    }

    /**
     * 获取完整url
     */
    public function get_full_path($url){

        return $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'].$url;
    }

    /**
     * 获取default模块 model文件夹
     */
    public function model($model) {
        static $objArray = array();
        $className = $model . 'Model';

        require_once APP_PATH . 'default/models/' . $className . '.class.php';

        if (!is_object($objArray[$className])) {
            if (!class_exists($className)) {
                throw new \Exception(C('_APP_NAME') . '/' . $className . '.class.php 模型类不存在');
            }
            $className = class_exists('MY_'. $className) ? 'MY_'. $className : $className;
            $objArray[$className] = new $className();
        }
        return $objArray[$className];
    }
}
