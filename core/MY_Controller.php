<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 控制器基类 抽象类
 */
class MY_Controller extends CI_Controller
{
    public function __construct(){
        parent::__construct();
        // 安装检测
        if (!file_exists(ROOT_PATH . 'data/install.lock') && !defined('NO_CHECK_INSTALL')) {
            $this->load->install();
        }
        $this->definition();
    }

    private function definition(){
        define('CONTROLLER_NAME', strtolower($this->router->class));
        define('ACTION_NAME', strtolower($this->router->method));
    }
}

class Controller extends MY_Controller
{
    protected $model = NULL;

    /**
     * 构造函数 取得模板对象实例
     * @access public
     */
    public function __construct()
    {
        parent::__construct();
        $this->model = new Model();
    }

    /**
     * 出错之后返回json数据
     * @param $msg
     * @param $code
     */
    protected function jserror($msg, $code)
    {
        echo json_encode(array(
            "error" => '1',
            'code' => $code,
            "message" => $msg
        ));
        exit();
    }

    /**
     * 成功之后返回json
     * @param $msg
     * @param array $data
     */
    protected function jssuccess($msg, $data = array())
    {
        echo json_encode(array(
            "error" => '0',
            "message" => $msg,
            "data" => $data
        ));
        exit();
    }

    // /**
    //  *  获取输出页面内容
    //  * 调用内置的模板引擎fetch方法，
    //  * @access protected
    //  * @param string $templateFile 指定要调用的模板文件
    //  * 默认为空 由系统自动定位模板文件
    //  * @param string $content 模板输出内容
    //  * @param string $prefix 模板缓存前缀*
    //  * @return string
    //  */
    // protected function fetch($templateFile='',$content='',$prefix='') {
    //     return $this->view->fetch($templateFile,$content,$prefix);
    // }

    // /**
    //  *  创建静态页面
    //  * @access protected
    //  * @htmlfile 生成的静态文件名称
    //  * @htmlpath 生成的静态文件路径
    //  * @param string $templateFile 指定要调用的模板文件
    //  * 默认为空 由系统自动定位模板文件
    //  * @return string
    //  */
    // protected function buildHtml($htmlfile='',$htmlpath='',$templateFile='') {
    //     $content    =   $this->fetch($templateFile);
    //     $htmlpath   =   !empty($htmlpath)?$htmlpath:DATA_PATH.'caches/html/';
    //     $htmlfile   =   $htmlpath.$htmlfile.'.html';
    //     file_put_contents($htmlfile, $content);
    //     return $content;
    // }

    // /**
    //  * 模板主题设置
    //  * @access protected
    //  * @param string $theme 模版主题
    //  * @return Action
    //  */
    // protected function theme($theme){
    //     $this->view->theme($theme);
    //     return $this;
    // }

    // /**
    //  * 模板变量赋值
    //  * @access protected
    //  * @param mixed $name 要显示的模板变量
    //  * @param mixed $value 变量的值
    //  * @return Action
    //  */
    // protected function assign($name,$value='') {
    //     $this->view->assign($name,$value);
    //     return $this;
    // }

    // public function __set($name,$value) {
    //     $this->assign($name,$value);
    // }

    // /**
    //  * 取得模板显示变量的值
    //  * @access protected
    //  * @param string $name 模板显示变量
    //  * @return mixed
    //  */
    // public function get($name='') {
    //     //return $this->view->get($name);
    // }

    // public function __get($name) {
    //     return $this->get($name);
    // }

    // /**
    //  * 检测模板变量的值
    //  * @access public
    //  * @param string $name 名称
    //  * @return boolean
    //  */
    // public function __isset($name) {
    //     return $this->get($name);
    // }

    // /**
    //  * 魔术方法 有不存在的操作的时候执行
    //  * @access public
    //  * @param string $method 方法名
    //  * @param array $args 参数
    //  * @return mixed
    //  */
    // public function __call($method,$args) {
    //     if( 0 === strcasecmp($method, /*ACTION_NAME.*/config_item('ACTION_SUFFIX'))) {
    //         if(method_exists($this,'_empty')) {
    //             // 如果定义了_empty操作 则调用
    //             $this->_empty($method,$args);
    //         }elseif(file_exists_case($this->view->parseTemplate())){
    //             // 检查是否存在默认模版 如果有直接输出模版
    //             $this->display();
    //         }else{
    //             //echo (lang('_ERROR_ACTION_').':'.ACTION_NAME);
    //             echo 'aa';
    //         }
    //     }else{
    //         echo(__CLASS__.':'.$method.'_METHOD_NOT_EXIST_');
    //         return;
    //     }
    // }

    // /**
    //  * 操作错误跳转的快捷方法
    //  * @access protected
    //  * @param string $message 错误信息
    //  * @param string $jumpUrl 页面跳转地址
    //  * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
    //  * @return void
    //  */
    // protected function error($message='',$jumpUrl='',$ajax=false) {
    //     $this->dispatchJump($message,0,$jumpUrl,$ajax);
    // }

    // /**
    //  * 操作成功跳转的快捷方法
    //  * @access protected
    //  * @param string $message 提示信息
    //  * @param string $jumpUrl 页面跳转地址
    //  * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
    //  * @return void
    //  */
    // protected function success($message='',$jumpUrl='',$ajax=false) {
    //     $this->dispatchJump($message,1,$jumpUrl,$ajax);
    // }

    // /**
    //  * Ajax方式返回数据到客户端
    //  * @access protected
    //  * @param mixed $data 要返回的数据
    //  * @param String $type AJAX返回数据格式
    //  * @param int $json_option 传递给json_encode的option参数
    //  * @return void
    //  */
    // protected function ajaxReturn($data,$type='',$json_option=0) {
    //     if(empty($type)) $type = 'JSON';
    //     switch (strtoupper($type)){
    //         case 'JSON' :
    //             // 返回JSON数据格式到客户端 包含状态信息
    //             header('Content-Type:application/json; charset=utf-8');
    //             exit(json_encode($data,$json_option));
    //         case 'XML'  :
    //             // 返回xml格式数据
    //             header('Content-Type:text/xml; charset=utf-8');
    //             exit(xml_encode($data));
    //         case 'JSONP':
    //             // 返回JSON数据格式到客户端 包含状态信息
    //             header('Content-Type:application/json; charset=utf-8');
    //             $handler  =   isset($_GET['callback']) ? $_GET['callback'] : 'jsonpReturn';
    //             exit($handler.'('.json_encode($data,$json_option).');');
    //         case 'EVAL' :
    //             // 返回可执行的js脚本
    //             header('Content-Type:text/html; charset=utf-8');
    //             exit($data);
    //     }
    // }

    /**
     * Action跳转(URL重定向） 支持指定模块和延时跳转
     * @access protected
     * @param string $url 跳转的URL表达式
     * @param array $params 其它URL参数
     * @param integer $delay 延时跳转的时间 单位为秒
     * @param string $msg 跳转提示信息
     * @return void
     */
    protected function redirect($url,$params=array(),$delay=0,$msg='') {
        $url    =   url($url,$params);
        redirect($url,$delay,$msg);
    }

    // /**
    //  * 默认跳转操作 支持错误导向和正确跳转
    //  * 调用模板显示 默认为public目录下面的success页面
    //  * 提示页面为可配置 支持模板标签
    //  * @param string $message 提示信息
    //  * @param Boolean $status 状态
    //  * @param string $jumpUrl 页面跳转地址
    //  * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
    //  * @access private
    //  * @return void
    //  */
    // private function dispatchJump($message,$status=1,$jumpUrl='',$ajax=false) {
    //     if(true === $ajax || IS_AJAX) {// AJAX提交
    //         $data           =   is_array($ajax)?$ajax:array();
    //         $data['info']   =   $message;
    //         $data['status'] =   $status;
    //         $data['url']    =   $jumpUrl;
    //         $this->ajaxReturn($data);
    //     }
    //     if(is_int($ajax)) $this->assign('waitSecond',$ajax);
    //     if(!empty($jumpUrl)) $this->assign('jumpUrl',$jumpUrl);
    //     // 提示标题
    //     $this->assign('msgTitle', $status ? '操作成功' : '操作失败');
    //     //如果设置了关闭窗口，则提示完毕后自动关闭窗口
    //     if($this->get('closeWin')) $this->assign('jumpUrl','javascript:window.close();');
    //     $this->assign('status',$status); // 状态
    //     if($status) { //发送成功信息
    //         $this->assign('message',$message);// 提示信息
    //         // 成功操作后默认停留1秒
    //         if(!isset($this->waitSecond))    $this->assign('waitSecond','1');
    //         // 默认操作成功自动返回操作前页面
    //         if(!isset($this->jumpUrl)) $this->assign("jumpUrl", $_SERVER["HTTP_REFERER"]);
    //         $this->display('success');
    //     }else{
    //         $this->assign('error',$message);// 提示信息
    //         //发生错误时候默认停留3秒
    //         if(!isset($this->waitSecond))    $this->assign('waitSecond','3');
    //         // 默认发生错误的话自动返回上页
    //         if(!isset($this->jumpUrl)) $this->assign('jumpUrl',"javascript:history.back(-1);");
    //         $this->display('error');
    //         // 中止执行  避免出错后继续执行
    //         exit ;
    //     }
    // }

    /**
     * 加载控制器语言包
     */
    protected function controller_lang(){
        // 载入控制器语言文件
        $controller_lang_file = BASE_PATH . 'language/' . C('lang') . '/'. CONTROLLER_NAME .'.php';
        if(file_exists($controller_lang_file)){
            $controller_lang = require($controller_lang_file);
            L($controller_lang);
        }
        $flow_lang_file = BASE_PATH . 'language/' . C('lang') . '/shopping_flow.php';
        if(file_exists($flow_lang_file)){
            $flow_lang = require($flow_lang_file);
            L($flow_lang);
        }
    }
}

/**
 * 后端控制器基类 抽象类
 */
abstract class AdminController extends Controller
{

    /**
     * 视图实例对象
     * @var view
     * @access protected
     */
    protected $themes = 'admin';

    public function __construct(){
        parent::__construct();
        $this->checkLogin();
    }

    /**
     * 模板显示 调用内置的模板引擎显示方法，
     * @access protected
     * @param string $templateFile 指定要调用的模板文件
     * 默认为空 由系统自动定位模板文件
     * @param string $charset 输出编码
     * @param string $contentType 输出类型
     * @param string $content 输出内容
     * @param string $prefix 模板缓存前缀
     * @return void
     */
    protected function display($templateFile = '', $vars = array(), $return = FALSE)
    {
        if (CONTROLLER_NAME == 'welcome' && ACTION_NAME == 'index') {
            $tpl = ACTION_NAME;
        } else {
            $tpl = CONTROLLER_NAME . '_' . ACTION_NAME;
        }
        $templateFile = empty($templateFile) ? $tpl : $templateFile;
        $this->load->view($this->themes . '/' . strtolower($templateFile) . '.html', $vars, $return);
    }

    /* 验证管理员身份 */
    private function checkLogin(){
        if (!isset($_SESSION['admin_id']) || intval($_SESSION['admin_id']) <= 0){
            //header('Location: privilege.php?act=login');
            //exit;
        }
    }
}

/**
 * 前端控制器基类 抽象类
 */
abstract class IndexController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->init();
        $this->controller_lang();
		$this->load->helper('lib');
        $this->set_theme(C('template'));
    }

    protected function is_cached($filename, $cache_id = ''){
        $this->load->tpl->is_cached($filename, $cache_id);
    }

    protected function fetch($filename, $cache_id = ''){
        $this->load->tpl->fetch($filename, $cache_id);
    }

    protected function assign($key = '', $value = ''){
        $this->load->tpl->assign($key, $value);
    }

    protected function display($filename = '', $cache_id = ''){
        $this->load->tpl->display($filename, $cache_id);
    }

    private function set_theme($theme = 'default'){
        define('__ROOT__', base_url());
        define('__PUBLIC__', base_url('data/assets'));
        define('__TPL__', base_url('themes/' . C('template')));
    }
}

/**
 * REST控制器类 抽象类
 */
abstract class RestController extends Controller
{
    // 当前请求类型
    protected $_method = '';
    // 当前请求的资源类型
    protected $_type = '';
    // REST允许的请求类型列表
    protected $allowMethod = array('get', 'post', 'put', 'delete');
    // REST默认请求类型
    protected $defaultMethod = 'get';
    // REST允许请求的资源类型列表
    protected $allowType = array('html', 'xml', 'json', 'rss');
    // 默认的资源类型
    protected $defaultType = 'html';
    // REST允许输出的资源类型列表
    protected $allowOutputType = array(
        'xml' => 'application/xml',
        'json' => 'application/json',
        'html' => 'text/html',
    );
    //安全密钥key
    protected $key = '1234561234564';
    public $user_id = 0;
    public $access_token = '';

    /**
     * 架构函数
     * @access public
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->init();
        $this->controller_lang();
        // 资源类型检测
        if ('' == REST_EXTEND) { // 自动检测资源类型
            $this->_type = $this->getAcceptType();
        } elseif (!in_array(REST_EXTEND, $this->allowType)) {
            // 资源类型非法 则用默认资源类型访问
            $this->_type = $this->defaultType;
        } else {
            $this->_type = REST_EXTEND;
        }

        // 请求方式检测
        $method = strtolower(REQUEST_METHOD);
        if (!in_array($method, $this->allowMethod)) {
            // 请求方式非法 则用默认请求方法
            $method = $this->defaultMethod;
        }
        $this->_method = $method;
        //print_r($_SESSION);
        $session = I_A('session');
        $session = $session && !is_array($session) ? json_decode($session, true) : $session;
        //$this->user_id = $session['uid'] = 1;
        //$this->user_id = 1;
        //$this->access_token = $session['token'] = 'c6a6a4d1c53bcfb641eacfcc77d33619';
        //$this->access_token = 'c6a6a4d1c53bcfb641eacfcc77d33619';
        if(isset($session['uid']) && isset($session['token'])){
            $this->user_id = $session['uid'];
            $this->access_token = $session['token'];
        }
    }

    /**
     * 魔术方法 有不存在的操作的时候执行
     * @access public
     * @param string $method 方法名
     * @param array $args 参数
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (0 === strcasecmp($method, ACTION_NAME . C('ACTION_SUFFIX'))) {
            if (method_exists($this, $method . '_' . $this->_method . '_' . $this->_type)) { // RESTFul方法支持
                $fun = $method . '_' . $this->_method . '_' . $this->_type;
                $this->$fun();
            } elseif ($this->_method == $this->defaultMethod && method_exists($this, $method . '_' . $this->_type)) {
                $fun = $method . '_' . $this->_type;
                $this->$fun();
            } elseif ($this->_type == $this->defaultType && method_exists($this, $method . '_' . $this->_method)) {
                $fun = $method . '_' . $this->_method;
                $this->$fun();
            } elseif (method_exists($this, '_empty')) {
                // 如果定义了_empty操作 则调用
                $this->_empty($method, $args);
            } elseif (file_exists_case($this->view->parseTemplate())) {
                // 检查是否存在默认模版 如果有直接输出模版
                $this->display();
            } else {
                die('_ERROR_ACTION_:' . ACTION_NAME);
            }
        }
    }

    /**
     * 获取当前请求的Accept头信息
     * @return string
     */
    protected function getAcceptType()
    {
        $type = array(
            'xml' => 'application/xml,text/xml,application/x-xml',
            'json' => 'application/json,text/x-json,application/jsonrequest,text/json',
            'js' => 'text/javascript,application/javascript,application/x-javascript',
            'css' => 'text/css',
            'rss' => 'application/rss+xml',
            'yaml' => 'application/x-yaml,text/yaml',
            'atom' => 'application/atom+xml',
            'pdf' => 'application/pdf',
            'text' => 'text/plain',
            'png' => 'image/png',
            'jpg' => 'image/jpg,image/jpeg,image/pjpeg',
            'gif' => 'image/gif',
            'csv' => 'text/csv',
            'html' => 'text/html,application/xhtml+xml,*/*'
        );

        foreach ($type as $key => $val) {
            $array = explode(',', $val);
            foreach ($array as $k => $v) {
                if (stristr($_SERVER['HTTP_ACCEPT'], $v)) {
                    return $key;
                }
            }
        }
        return false;
    }

    // 发送Http状态信息
    protected function sendHttpStatus($code)
    {
        static $_status = array(
            // Informational 1xx
            100 => 'Continue',
            101 => 'Switching Protocols',
            // Success 2xx
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            // Redirection 3xx
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Moved Temporarily ',  // 1.1
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            // 306 is deprecated but reserved
            307 => 'Temporary Redirect',
            // Client Error 4xx
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            // Server Error 5xx
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            509 => 'Bandwidth Limit Exceeded'
        );
        if (isset($_status[$code])) {
            header('HTTP/1.1 ' . $code . ' ' . $_status[$code]);
            // 确保FastCGI模式下正常
            header('Status:' . $code . ' ' . $_status[$code]);
        }
    }

    /**
     * 编码数据
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type 返回类型 JSON XML
     * @return string
     */
    protected function encodeData($data, $type = '')
    {
        if (empty($data)) return '';
        if ('json' == $type) {
            // 返回JSON数据格式到客户端 包含状态信息
            $data = json_encode($data);
        } elseif ('xml' == $type) {
            // 返回xml格式数据
            $data = xml_encode($data);
        } elseif ('php' == $type) {
            $data = serialize($data);
        }// 默认直接输出
        $this->setContentType($type);
        //header('Content-Length: ' . strlen($data));
        return $data;
    }

    /**
     * 设置页面输出的CONTENT_TYPE和编码
     * @access public
     * @param string $type content_type 类型对应的扩展名
     * @param string $charset 页面输出编码
     * @return void
     */
    public function setContentType($type, $charset = 'utf-8')
    {
        if (headers_sent()) return;
        $type = strtolower($type);
        if (isset($this->allowOutputType[$type])) //过滤content_type
            header('Content-Type: ' . $this->allowOutputType[$type] . '; charset=' . $charset);
    }

    /**
     * 输出返回数据
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type 返回类型 JSON XML
     * @param integer $code HTTP状态
     * @return void
     */
    protected function response($data, $code = 200, $type = 'JSON', $extends = array())
    {
        $this->sendHttpStatus($code);
        if (!is_array($data)) {
            $status = array(
                'status' => array(
                    'code' => 0,
                    'error_message' => $data,
                    'url' => isset($extends['url']) ? $extends['url'] : ''
                )
            );
            exit($this->encodeData($status, strtolower($type)));
        }
        if (isset($data['response'])) {
            $data = $data['response'];
        }
        $data = array_merge(array('response' => $data), array('status' => array('code' => 1)));
        if(is_array($extends) && !empty($extends)){
            $data = array_merge($data, $extends);
        }
        exit($this->encodeData($data, strtolower($type)));
    }

    /**
     * 验证access_token
     * $url 错误提示后跳转链接
     */
    protected function checkAuth($url = '../'){
        $token = $this->getToken($this->user_id);
        if(!$_SESSION['user_id'] || !$this->user_id || !$this->access_token || $this->access_token != $token){
            $this->response('access_token无效或者用户未登录', 200, 'JSON', array('url'=>$url));
        }
    }

    /**
     * 获取access_token
     */
    protected function getToken($user_id){
        $this->load->driver('cache', array('adapter'=>'file'));
        $authname = 'auth_access_token'.$user_id;

        $token = $this->cache->get($authname);
        if(!$token && $user_id == $_SESSION['user_id']){
            $issucess = $this->setToken($user_id);
            if($issucess){
                $token = $this->cache->get($authname);
            }
            else{
                return false;
            }
        }
        return $token;
    }

    /**
     * 设置access_token
     */
    protected function setToken($user_id){
        $this->load->driver('cache', array('adapter'=>'file'));
        $authname = 'auth_access_token'.$user_id;
        $access_token = md5($user_id.$this->key);

        $token = $this->cache->save($authname, $access_token, 7200);

        return $token;

    }

    /**
     * 删除指定access_token
     * @return bool
     */
    protected function removeToken($user_id){
        $this->load->driver('cache', array('adapter'=>'file'));
        $authname = 'auth_access_token'.$user_id;

        if($token = $this->cache->get($authname)){
            return $this->cache->delete($authname);
        }
        return false;
    }
}

/**
 * 用户中心基类
 */
class UserBaseController extends RestController
{
    public function __construct(){
        parent::__construct();
    }

    /**
     * 获取access_token
     */
    public function get_token(){
        if(!$this->user_id){
            $this->response('获取access_token无效或者用户未登录');
        }
        dump($this->getToken($this->user_id));
    }
}

