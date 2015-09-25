<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Loader extends CI_Loader
{
    public $ecs = null;
    public $db = null;
    public $err = null;
    public $sess = null;
    public $tpl = null;
    public $user = null;
    private static $_map = array();

    public function __construct(){
        parent::__construct();
        spl_autoload_register(array($this, 'autoload'));
    }

    public function start(){
        $this->set_ini();
        $this->init();
        $this->init_view();
        $this->init_user();
        $this->init_gzip();
    }
    
    public function db(){
        return $this->db;
    }

    /**
     * 初始化设置
     */
    private function set_ini(){
        @ini_set('memory_limit', '128M');
        @ini_set('session.cache_expire',  180);
        @ini_set('session.use_trans_sid', 0);
        @ini_set('session.use_cookies',   1);
        @ini_set('session.auto_start',    0);
        @ini_set('display_errors',        1);
    }

    /**
     * 应用程序初始化
     * @access public
     * @return void
     */
    private function init()
    {
        $ecsdb = dirname(ROOT_PATH) . '/data/config.php';
        if(file_exists($ecsdb)){
            require($ecsdb);
        }else{
            die('Can\'t connect to the database.');
        }
        date_default_timezone_set($timezone);
        define('PHP_SELF', SELF);

        $helper_list = array('time', 'base', 'common', 'main', 'insert', 'goods', 'article');
        $this->helper($helper_list);

        $db_host = substr($db_host, 0, strpos($db_host, ':'));
        $this->ecs = new ecshop($db_name, $prefix);
        $this->db = new mysql($db_host, $db_user, $db_pass, $db_name);
        $this->db->set_disable_cache_tables(array($this->ecs->table('sessions'), $this->ecs->table('sessions_data'), $this->ecs->table('cart')));
        $this->err = new error('message.dwt');

        C(load_config());
        L(require(ROOT_PATH . 'language/' . C('lang') . '/common.php'));

        // 商店关闭了，输出关闭的消息
        if (C('shop_closed') == 1) {
            header('Content-type: text/html; charset=' . CHARSET);
            die('<p>' . L('shop_closed') . '</p><p>' . C('close_comment') . '</p>');
        }

        if (is_spider()) {
            // 如果是蜘蛛的访问，那么默认为访客方式，并且不记录到日志中
            if (!defined('INIT_NO_USERS')) {
                define('INIT_NO_USERS', true);
                // 整合UC后，如果是蜘蛛访问，初始化UC需要的常量
                if (C('integrate_code') == 'ucenter') {
                    $this->user = &init_users();
                }
            }
            $_SESSION = array();
            $_SESSION['user_id'] = 0;
            $_SESSION['user_name'] = '';
            $_SESSION['email'] = '';
            $_SESSION['user_rank'] = 0;
            $_SESSION['discount'] = 1.00;
        }

        if (!defined('INIT_NO_USERS')) {
            $this->sess = new session($this->db, $this->ecs->table('sessions'), $this->ecs->table('sessions_data'));
            define('SESS_ID', $this->sess->get_session_id());
        }
    }

    private function init_view(){
        if (!defined('INIT_NO_SMARTY')) {
            header('Cache-control: private');
            header('Content-type: text/html; charset=' . CHARSET);

            $this->tpl = new template;
            $this->tpl->cache_lifetime = C('cache_time');
            $this->tpl->template_dir = ROOT_PATH . 'views/' . C('template');
            $this->tpl->cache_dir = ROOT_PATH . 'caches/views';
            $this->tpl->compile_dir = ROOT_PATH . 'caches/views/compiled';

            if (APP_DEBUG) {
                $this->tpl->direct_output = true;
                $this->tpl->force_compile = true;
            } else {
                $this->tpl->direct_output = false;
                $this->tpl->force_compile = false;
            }

            $this->tpl->assign('lang', L());
            $this->tpl->assign('ecs_charset', CHARSET);
            if (C('stylename') !== '') {
                $this->tpl->assign('ecs_css_path', 'views/' . C('template') . '/css/style_' . C('stylename') . '.css');
            } else {
                $this->tpl->assign('ecs_css_path', 'views/' . C('template') . '/css/style.css');
            }
        }
    }

    private function init_user(){
        if (!defined('INIT_NO_USERS')) {
            // 会员信息
            $this->user =& init_users();

            if (!isset($_SESSION['user_id'])) {
                /* 获取投放站点的名称 */
                $site_name = isset($_GET['from']) ? htmlspecialchars($_GET['from']) : addslashes(L('self_site'));
                $from_ad = !empty($_GET['ad_id']) ? intval($_GET['ad_id']) : 0;

                $_SESSION['from_ad'] = $from_ad; // 用户点击的广告ID
                $_SESSION['referer'] = stripslashes($site_name); // 用户来源

                unset($site_name);

                if (!defined('INGORE_VISIT_STATS')) {
                    visit_stats();
                }
            }

            if (empty($_SESSION['user_id'])) {
                if ($this->user->get_cookie()) {
                    /* 如果会员已经登录并且还没有获得会员的帐户余额、积分以及优惠券 */
                    if ($_SESSION['user_id'] > 0) {
                        update_user_info();
                    }
                } else {
                    $_SESSION['user_id'] = 0;
                    $_SESSION['user_name'] = '';
                    $_SESSION['email'] = '';
                    $_SESSION['user_rank'] = 0;
                    $_SESSION['discount'] = 1.00;
                    if (!isset($_SESSION['login_fail'])) {
                        $_SESSION['login_fail'] = 0;
                    }
                }
            }

            // 设置推荐会员
            if (isset($_GET['u'])) {
                set_affiliate();
            }

            // session 不存在，检查cookie
            if (!empty($_COOKIE['ECS']['user_id']) && !empty($_COOKIE['ECS']['password'])) {
                // 找到了cookie, 验证cookie信息
                $condition = array(
                    'user_id' => intval($_COOKIE['ECS']['user_id']),
                    'password' => $_COOKIE['ECS']['password']
                );
                $row = $this->db->get_where('users', $condition)->row_array();

                if (!$row) {
                    $time = time() - 3600;
                    setcookie("ECS[user_id]", '', $time, '/');
                    setcookie("ECS[password]", '', $time, '/');
                } else {
                    $_SESSION['user_id'] = $row['user_id'];
                    $_SESSION['user_name'] = $row['user_name'];
                    update_user_info();
                }
            }

            if (isset($this->tpl)) {
                $this->tpl->assign('ecs_session', $_SESSION);
            }
        }
    }
    
    //判断是否支持 Gzip 模式
    private function init_gzip(){
        if (!defined('INIT_NO_SMARTY') && gzip_enabled()) {
            ob_start('ob_gzhandler');
        } else {
            ob_start();
        }
    }

    /**
     * 实现类的自动加载
     * @param $class
     */
    private function autoload($class)
    {
        // 检查是否存在映射
        if (!isset(self::$_map[$class])) {
            //$class = ucfirst($class);
            $array = array(
                ROOT_PATH . 'classes/' . $class . '.php',
                ROOT_PATH . 'interface/' . $class . '.php',
                ROOT_PATH . 'vendor/' . $class . '.php',
            );
            foreach ($array as $file) {
                if (is_file($file)) {
                    self::$_map[$class] = $file;
                }
            }
        }
        include self::$_map[$class];
    }
}