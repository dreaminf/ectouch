<?php

namespace app\modules\admin\controllers;

use yii\helpers\Url;
use app\libraries\Shop;
use app\libraries\Error;
use app\libraries\Mysql;
use app\libraries\Captcha;
use app\libraries\Session;
use app\libraries\Template;
use yii\web\Controller as BaseController;

/**
 * Class Controller
 * @package app\modules\admin\controllers
 */
class Controller extends BaseController
{
    protected $ecs;
    protected $db;
    protected $err;
    protected $sess;
    protected $smarty;
    protected $_CFG;

    public function init()
    {
        define('ECS_ADMIN', true);
        $urls = parse_url($_SERVER['REQUEST_URI']);
        define('PHP_SELF', (stripos($urls['path'], '.php') !== false) ? basename(strtolower($urls['path']), '.php') : 'index');

        /**
         * 重新获取 REQUEST 参数
         */
        $_GET = app('request')->get();
        $_POST = app('request')->post();
        $_REQUEST = $_GET + $_POST;
        $_REQUEST['act'] = isset($_REQUEST['act']) ? $_REQUEST['act'] : 'list';

        /**
         * 加载必须 Helper 函数库
         */
        load_helper(['time', 'base', 'common']);
        load_helper(['main', 'exchange'], 'admin');

        /* 初始化 Shop 类 */
        $this->ecs = $GLOBALS['ecs'] = new Shop();
        define('DATA_DIR', $this->ecs->data_dir());
        define('IMAGE_DIR', $this->ecs->image_dir());

        /* 初始化数据库类 */
        $this->db = $GLOBALS['db'] = new Mysql();

        /* 创建错误处理对象 */
        $this->err = $GLOBALS['err'] = new Error();

        /* 载入系统参数 */
        $this->_CFG = $GLOBALS['_CFG'] = load_config();

        /* 初始化session */
        $this->sess = $GLOBALS['sess'] = new Session($this->db, $this->ecs->table('sessions'), $this->ecs->table('sessions_data'), 'ECSCP_ID');

        // TODO : 登录部分准备拿出去做，到时候把以下操作一起挪过去
        if ($_REQUEST['act'] == 'captcha') {
            $img = new Captcha(public_path('data/captcha/'));
            @ob_end_clean(); //清除之前出现的多余输入
            $img->generate_image();
            exit;
        }

        load_lang(['common', 'log_action', PHP_SELF], 'admin');

        define('__ROOT__', asset('/'));
        define('__PUBLIC__', asset('/static'));
        define('__TPL__', asset('/static/dashboard'));

        /* 创建 Smarty 对象。*/
        $this->smarty = $GLOBALS['smarty'] = new Template();
        $this->smarty->template_dir = dirname(__DIR__) . '/Views';
        $this->smarty->compile_dir = storage_path('framework/temp/compiled/admin');
        if (config('app.debug')) {
            $this->smarty->force_compile = true;
        }

        $this->smarty->assign('lang', $GLOBALS['_LANG']);
        $this->smarty->assign('help_open', $GLOBALS['_CFG']['help_open']);
        $this->smarty->assign('enable_order_check', $GLOBALS['_CFG']['enable_order_check']);

        /* 验证管理员身份 */
        if ((!session('?admin_id') || intval(session('admin_id')) <= 0) &&
            $_REQUEST['act'] != 'login' && $_REQUEST['act'] != 'signin' &&
            $_REQUEST['act'] != 'forget_pwd' && $_REQUEST['act'] != 'reset_pwd' && $_REQUEST['act'] != 'check_order') {
            /* session 不存在，检查cookie */
            $cp_admin_id = cookie('cp_admin_id');
            $cp_admin_pass = cookie('cp_admin_pass');
            if (!empty($cp_admin_id) && !empty($cp_admin_pass)) {
                // 找到了cookie, 验证cookie信息
                $sql = 'SELECT user_id, user_name, password, action_list, last_login ' .
                    ' FROM ' . $this->ecs->table('admin_user') .
                    " WHERE user_id = '" . intval($cp_admin_id) . "'";
                $row = $this->db->getRow($sql);

                if (!$row) {
                    // 没有找到这个记录
                    cookie('cp_admin_id', null);
                    cookie('cp_admin_pass', null);

                    if (!empty($_REQUEST['is_ajax'])) {
                        return make_json_error($GLOBALS['_LANG']['priv_error']);
                    } else {
                        $this->redirect('privilege.php?act=login');
                    }
                } else {
                    // 检查密码是否正确
                    if (md5($row['password'] . $GLOBALS['_CFG']['hash_code']) == $cp_admin_pass) {
                        !isset($row['last_time']) && $row['last_time'] = '';
                        set_admin_session($row['user_id'], $row['user_name'], $row['action_list'], $row['last_time']);

                        // 更新最后登录时间和IP
                        $this->db->query('UPDATE ' . $this->ecs->table('admin_user') .
                            " SET last_login = '" . gmtime() . "', last_ip = '" . real_ip() . "'" .
                            " WHERE user_id = '" . session('admin_id') . "'");
                    } else {
                        cookie('cp_admin_id', null);
                        cookie('cp_admin_pass', null);

                        if (!empty($_REQUEST['is_ajax'])) {
                            return make_json_error($GLOBALS['_LANG']['priv_error']);
                        } else {
                            $this->redirect('privilege.php?act=login');
                        }
                    }
                }
            } else {
                if (!empty($_REQUEST['is_ajax'])) {
                    return make_json_error($GLOBALS['_LANG']['priv_error']);
                } else {
                    $this->redirect('privilege.php?act=login');
                }
            }
        }
    }

    /**
     * URL重定向
     * @param array|string $url
     * @param int $statusCode
     * @return \yii\web\Response
     */
    public function redirect($url, $statusCode = 302)
    {
        $url = ADMIN_PATH . '/' . $url;
        return parent::redirect($url, $statusCode);
    }
}
