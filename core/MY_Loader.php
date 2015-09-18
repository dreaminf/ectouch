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

    public function __construct()
    {
        parent::__construct();
        spl_autoload_register(array($this, 'autoload'));
    }

    /**
     * 应用程序初始化
     * @access public
     * @return void
     */
    public function init()
    {
        // 加载数据库
        $db_config = require(DATA_PATH . 'config.php');
        // 初始化设置
        @ini_set('memory_limit', '128M');
        @ini_set('session.cache_expire',  180);
        @ini_set('session.use_trans_sid', 0);
        @ini_set('session.use_cookies',   1);
        @ini_set('session.auto_start',    0);
        @ini_set('display_errors',        1);
        // 设置时区
        date_default_timezone_set('Asia/Shanghai');
        // 加载函数库
        $helper_list = array('time', 'base', 'common', 'main', 'insert', 'goods', 'article');
        $this->helper($helper_list);
        // 创建SHOP对象
        $this->ecs = new ecshop($db_config['DB_NAME'], $db_config['DB_PREFIX']);
        // 初始化数据库类
        $this->db = new mysql($db_config['DB_HOST'], $db_config['DB_USER'], $db_config['DB_PWD'], $db_config['DB_NAME']);
        $this->db->set_disable_cache_tables(array($this->ecs->table('sessions'), $this->ecs->table('sessions_data'), $this->ecs->table('cart')));
        // 创建错误处理对象
        $this->err = new error('message.dwt');
        // 载入系统参数
        $global_config = load_config();
        C($global_config);
        // 载入语言文件
        $global_lang = require(BASE_PATH . 'language/' . C('lang') . '/common.php');
        L($global_lang);

        if (C('shop_closed') == 1) {
            // 商店关闭了，输出关闭的消息
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

        if (isset($_SERVER['PHP_SELF'])) {
            $_SERVER['PHP_SELF'] = htmlspecialchars($_SERVER['PHP_SELF']);
        }

        if (!defined('INIT_NO_SMARTY')) {
            header('Cache-control: private');
            header('Content-type: text/html; charset=' . CHARSET);

            // 创建 Smarty 对象。
            $this->tpl = new template;

            $this->tpl->cache_lifetime = C('cache_time');
            $this->tpl->template_dir = ROOT_PATH . 'themes/' . C('template');
            $this->tpl->cache_dir = STORAGE_PATH . 'caches';
            $this->tpl->compile_dir = STORAGE_PATH . 'compiled';

            if (APP_DEBUG) {
                $this->tpl->direct_output = true;
                $this->tpl->force_compile = true;
            } else {
                $this->tpl->direct_output = false;
                $this->tpl->force_compile = false;
            }

            $this->tpl->assign('lang', L());
            $this->tpl->assign('ecs_charset', CHARSET);
            if (!empty($_CFG['stylename'])) {
                $this->tpl->assign('ecs_css_path', 'themes/' . C('template') . '/css/style_' . C('stylename') . '.css');
            } else {
                $this->tpl->assign('ecs_css_path', 'themes/' . C('template') . '/css/style.css');
            }

        }

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
                $sql = 'SELECT user_id, user_name, password ' .
                    ' FROM ' . $this->ecs->table('users') .
                    " WHERE user_id = '" . intval($_COOKIE['ECS']['user_id']) . "' AND password = '" . $_COOKIE['ECS']['password'] . "'";

                $row = $this->db->GetRow($sql);

                if (!$row) {
                    // 没有找到这个记录
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

        // 判断是否支持 Gzip 模式
        if (!defined('INIT_NO_SMARTY') && gzip_enabled()) {
            ob_start('ob_gzhandler');
        } else {
            ob_start();
        }

    }

    /**
     * 实现应用程序自动安装
     */
    public function install(){
        $base = new Model();
        $bak = new Dbbak($base->config['DB_HOST'], $base->config['DB_USER'], $base->config['DB_PWD'], $base->config['DB_NAME'], $base->config['DB_CHARSET']);
        //查找数据库内所有数据表
        $tables = $bak->getTables();
        $tables = empty($tables) ? array():$tables;
        //导入数据
        if(!in_array($base->config['DB_PREFIX'].'shop_config', $tables)){
            $this->importSql($base, array('structure.sql', 'data.sql'));
            // 插入管理员帐号
            $password = substr(md5(time()), 0, 8);
            $sql = "INSERT INTO ".$base->config['DB_PREFIX']."admin_user (user_name, email, password, add_time, action_list, nav_list)".
            "VALUES ('admin', 'ectouch@ecmoban.com', '".md5($password). "', " .time(). ", 'all', '$nav_list')";
            $base->query($sql);
        }
        if(!in_array($base->config['DB_PREFIX'].'wechat', $tables)){
            $this->importSql($base, array('install.sql', 'wechat.sql'));
        }
        if(isset($password)){
            echo '<h1>恭喜您安装成功！</h1>';
            echo '管理账户：admin<br>';
            echo '管理密码：'. $password. '(请牢记！相机拍一下更快哦。)';
            exit();
        }
    }

    /**
     * 获取数据库文件
     * @param array $config
     */
    private function importSql($db = array(), $sqls = array()){
        //设置表前缀
        $dbPrefix = $db->config['DB_PREFIX'];
        if (empty($dbPrefix)) {
            $dbPrefix = 'ecs_';
        }
        foreach ($sqls as $value) {
            $sql = DATA_PATH . 'install/' . $value;
            $sqlData = Install::mysql($sql, 'ecs_', $dbPrefix);
            if (!$this->runSql($db, $sqlData)) {
                exit($value.'数据导入失败，请检查后手动删除数据库重新安装！');
            }
        }
    }

    /**
     * 导入数据库文件
     * @param type $data
     * @param type $sqlArray
     * @return boolean
     */
    private function runSql($db, $sqlArray = array()) {
        if (is_array($sqlArray)){
            foreach ($sqlArray as $sql) {
                if (!$db->query($sql)) {
                    return false;
                }
            }
        }
        return true;
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
                BASE_PATH . 'classes/' . $class . '.php',
                BASE_PATH . 'interface/' . $class . '.php',
                BASE_PATH . 'vendor/' . $class . '.php',
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