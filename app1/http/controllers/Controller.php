<?php

namespace app\http\controllers;

use app\libraries\Shop;
use app\libraries\Error;
use app\libraries\Mysql;
use app\libraries\Session;
use app\libraries\Template;
use yii\web\Controller as BaseController;

class Controller extends BaseController
{
    protected $ecs;
    protected $db;
    protected $err;
    protected $sess;
    protected $smarty;
    protected $_CFG;
    protected $user;

    public function init()
    {
        define('PHP_SELF', basename(substr(basename($_SERVER['REQUEST_URI']), 0, stripos(basename($_SERVER['REQUEST_URI']), '?')), '.php'));

        $_GET = app('request')->get();
        $_POST = app('request')->post();
        $_REQUEST = $_GET + $_POST;
        $_REQUEST['act'] = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';

        load_helper(['time', 'base', 'common', 'main', 'insert', 'goods', 'article']);

        $this->ecs = $GLOBALS['ecs'] = new Shop();
        define('DATA_DIR', $this->ecs->data_dir());
        define('IMAGE_DIR', $this->ecs->image_dir());

        /* 初始化数据库类 */
        $this->db = $GLOBALS['db'] = new Mysql();

        /* 创建错误处理对象 */
        $this->err = $GLOBALS['err'] = new Error();

        /* 载入系统参数 */
        $this->_CFG = $GLOBALS['_CFG'] = load_config();

        /* 载入语言文件 */
        load_lang('common');

        if ($GLOBALS['_CFG']['shop_closed'] == 1) {
            /* 商店关闭了，输出关闭的消息 */
            header('Content-type: text/html; charset=' . CHARSET);
            die('<div style="margin: 150px; text-align: center; font-size: 14px"><p>' . $GLOBALS['_LANG']['shop_closed'] . '</p><p>' . $GLOBALS['_CFG']['close_comment'] . '</p></div>');
        }

        if (is_spider()) {
            /* 如果是蜘蛛的访问，那么默认为访客方式，并且不记录到日志中 */
            if (!defined('INIT_NO_USERS')) {
                define('INIT_NO_USERS', true);
                /* 整合UC后，如果是蜘蛛访问，初始化UC需要的常量 */
                if ($GLOBALS['_CFG']['integrate_code'] == 'ucenter') {
                    $this->user = $GLOBALS['user'] = &init_users();
                }
            }
            session(null);
            session('user_id', 0);
            session('user_name', '');
            session('email', '');
            session('user_rank', 0);
            session('discount', 1.00);
        }

        if (!defined('INIT_NO_USERS')) {
            $this->sess = $GLOBALS['sess'] = new Session($this->db, $this->ecs->table('sessions'), $this->ecs->table('sessions_data'));
            define('SESS_ID', $this->sess->get_session_id());
        }

        if (isset($_SERVER['PHP_SELF'])) {
            $_SERVER['PHP_SELF'] = htmlspecialchars($_SERVER['PHP_SELF']);
        }

        $app_mode = config('app.mode');
        if (($app_mode == 0 && is_mobile_device()) || $app_mode == 2) {
            $GLOBALS['_CFG']['template'] .= '/mobile';
        }

        define('__ROOT__', asset('/'));
        define('__PUBLIC__', asset('/static'));
        define('__TPL__', asset('/themes/' . $GLOBALS['_CFG']['template']));

        if (!defined('INIT_NO_SMARTY')) {
            header('Cache-control: private');
            header('Content-type: text/html; charset=' . CHARSET);

            $this->smarty = $GLOBALS['smarty'] = new Template();
            $this->smarty->cache_lifetime = $GLOBALS['_CFG']['cache_time'];
            $this->smarty->template_dir = resource_path('themes/' . $GLOBALS['_CFG']['template']);
            $this->smarty->cache_dir = storage_path('framework/temp/caches');
            $this->smarty->compile_dir = storage_path('framework/temp/compiled');

            if (config('app.debug')) {
                $this->smarty->direct_output = true;
                $this->smarty->force_compile = true;
            } else {
                $this->smarty->direct_output = false;
                $this->smarty->force_compile = false;
            }

            $this->smarty->assign('lang', $GLOBALS['_LANG']);
            $this->smarty->assign('ecs_charset', CHARSET);
            if (!empty($GLOBALS['_CFG']['stylename'])) {
                $ecs_css_path = asset('themes/' . $GLOBALS['_CFG']['template'] . '/style_' . $GLOBALS['_CFG']['stylename'] . '.css');
            } else {
                $ecs_css_path = asset('themes/' . $GLOBALS['_CFG']['template'] . '/style.css');
            }
            $this->smarty->assign('ecs_css_path', $ecs_css_path);
        }

        if (!defined('INIT_NO_USERS')) {
            /* 会员信息 */
            $this->user = $GLOBALS['user'] =& init_users();

            if (!session('?user_id')) {
                /* 获取投放站点的名称 */
                $site_name = isset($_GET['from']) ? htmlspecialchars($_GET['from']) : addslashes($GLOBALS['_LANG']['self_site']);
                $from_ad = !empty($_GET['ad_id']) ? intval($_GET['ad_id']) : 0;

                session('from_ad', $from_ad); // 用户点击的广告ID
                session('referer', stripslashes($site_name)); // 用户来源

                unset($site_name);

                if (!defined('INGORE_VISIT_STATS')) {
                    visit_stats();
                }
            }

            if (empty(session('user_id'))) {
                if ($this->user->get_cookie()) {
                    /* 如果会员已经登录并且还没有获得会员的帐户余额、积分以及优惠券 */
                    if (session('user_id') > 0) {
                        update_user_info();
                    }
                } else {
                    session('user_id', 0);
                    session('user_name', '');
                    session('email', '');
                    session('user_rank', 0);
                    session('discount', 1.00);
                    if (!session('?login_fail')) {
                        session('login_fail', 0);
                    }
                }
            }

            /**
             * 设置推荐会员
             */
            if (isset($_GET['u'])) {
                set_affiliate();
            }

            /**
             * session 不存在，检查cookie
             */
            $user_id = cookie('user_id');
            $password = cookie('password');
            if (!empty($user_id) && !empty($password)) {
                // 找到了cookie, 验证cookie信息
                $sql = 'SELECT user_id, user_name, password ' .
                    ' FROM ' . $this->ecs->table('users') .
                    " WHERE user_id = '" . intval($user_id) . "' AND password = '" . $password . "'";

                $row = $this->db->getRow($sql);

                if (!$row) {
                    // 没有找到这个记录
                    $time = 0;
                    cookie("user_id", null);
                    cookie("password", null);
                } else {
                    session('user_id', $row['user_id']);
                    session('user_name', $row['user_name']);
                    update_user_info();
                }
            }
        }
    }
}
