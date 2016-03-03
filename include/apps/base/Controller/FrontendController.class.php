<?php
namespace base\Controller;

class FrontendController extends BaseController {

	protected $ecs = null;
	protected $db = null;
	protected $err = null;
	protected $sess = null;
	protected $user = null;
	protected $tpl = null;

	public function __construct(){
		parent::__construct();
		$this->initGlobals();
	}

	private function initGlobals(){
		$php_self = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
		if ('/' == substr($php_self, -1)) {
		    $php_self .= 'index.php';
		}
		define('PHP_SELF', $php_self);

		require(CONF_PATH . 'constant.php');
		require(BASE_PATH . 'helpers/lib_time.php');
		require(BASE_PATH . 'helpers/lib_base.php');
		require(BASE_PATH . 'helpers/lib_common.php');
		require(BASE_PATH . 'helpers/lib_main.php');
		require(BASE_PATH . 'helpers/lib_insert.php');
		require(BASE_PATH . 'helpers/lib_goods.php');
		require(BASE_PATH . 'helpers/lib_article.php');

		/* 创建对象 */
		$GLOBALS['ecs'] = $this->ecs = new \classes\Ecs(C('DB_NAME'), C('DB_PREFIX'));
		define('DATA_DIR', $this->ecs->data_dir());
		define('IMAGE_DIR', $this->ecs->image_dir());

		/* 初始化数据库类 */
		$GLOBALS['db'] = $this->db = new \classes\Mysql(C('DB_HOST'), C('DB_USER'), C('DB_PWD'), C('DB_NAME'), C('DB_CHARSET'));
		$this->db->set_disable_cache_tables(array($this->ecs->table('sessions'), $this->ecs->table('sessions_data'), $this->ecs->table('cart')));

		/* 创建错误处理对象 */
		$GLOBALS['err'] = $err = new \classes\Error('message.dwt');

		/* 载入系统参数 */
		$GLOBALS['_CFG'] = $_CFG = load_ecs_config();
		//$_CFG['template'] = 'mobile';
		C($_CFG);

		/* 载入语言文件 */
		require(BASE_PATH . 'languages/' . $_CFG['lang'] . '/common.php');

		if ($_CFG['shop_closed'] == 1)
		{
		    /* 商店关闭了，输出关闭的消息 */
		    header('Content-type: text/html; charset='.EC_CHARSET);

		    die('<div style="margin: 150px; text-align: center; font-size: 14px"><p>' . $_LANG['shop_closed'] . '</p><p>' . $_CFG['close_comment'] . '</p></div>');
		}

		if (is_spider())
		{
		    /* 如果是蜘蛛的访问，那么默认为访客方式，并且不记录到日志中 */
		    if (!defined('INIT_NO_USERS'))
		    {
		        define('INIT_NO_USERS', true);
		        /* 整合UC后，如果是蜘蛛访问，初始化UC需要的常量 */
		        if($_CFG['integrate_code'] == 'ucenter')
		        {
		             $this->user = & init_users();
		        }
		    }
		    $_SESSION = array();
		    $_SESSION['user_id']     = 0;
		    $_SESSION['user_name']   = '';
		    $_SESSION['email']       = '';
		    $_SESSION['user_rank']   = 0;
		    $_SESSION['discount']    = 1.00;
		}

		if (!defined('INIT_NO_USERS'))
		{
		    /* 初始化session */
		    $GLOBALS['sess'] = $this->sess = new \classes\Session($this->db, $this->ecs->table('sessions'), $this->ecs->table('sessions_data'));

		    define('SESS_ID', $this->sess->get_session_id());
		}
		if(isset($_SERVER['PHP_SELF']))
		{
		    $_SERVER['PHP_SELF'] = htmlspecialchars($_SERVER['PHP_SELF']);
		}
		if (!defined('INIT_NO_SMARTY'))
		{
		    header('Cache-control: private');
		    header('Content-type: text/html; charset='.EC_CHARSET);

		    /* 创建 Smarty 对象。*/
		    $this->tpl = new \classes\Template;

		    $this->tpl->cache_lifetime = $_CFG['cache_time'];
		    $this->tpl->template_dir   = ROOT_PATH . 'themes/' . $_CFG['template'];
		    $this->tpl->cache_dir      = ROOT_PATH . 'data/caches/Temp/caches';
		    $this->tpl->compile_dir    = ROOT_PATH . 'data/caches/Temp/compiled';

		    if ((DEBUG_MODE & 2) == 2)
		    {
		        $this->tpl->direct_output = true;
		        $this->tpl->force_compile = true;
		    }
		    else
		    {
		        $this->tpl->direct_output = false;
		        $this->tpl->force_compile = false;
		    }
		    $GLOBALS['smarty'] = $this->tpl;

		    $this->tpl->assign('lang', $_LANG);
		    $this->tpl->assign('ecs_charset', EC_CHARSET);
		    if (!empty($_CFG['stylename']))
		    {
		        $this->tpl->assign('ecs_css_path', __ROOT__ . '/themes/' . $_CFG['template'] . '/style_' . $_CFG['stylename'] . '.css');
		    }
		    else
		    {
		        $this->tpl->assign('ecs_css_path', __ROOT__ . '/themes/' . $_CFG['template'] . '/style.css');
		    }
        define('__PUBLIC__', __ROOT__.'/data/statics/');
        define('__TPL__', __ROOT__.'/themes/' . $_CFG['template'] . '/');
		}

		if (!defined('INIT_NO_USERS'))
		{
		    /* 会员信息 */
		    $this->user =& init_users();

		    if (!isset($_SESSION['user_id']))
		    {
		        /* 获取投放站点的名称 */
		        $site_name = isset($_GET['from'])   ? htmlspecialchars($_GET['from']) : addslashes($_LANG['self_site']);
		        $from_ad   = !empty($_GET['ad_id']) ? intval($_GET['ad_id']) : 0;

		        $_SESSION['from_ad'] = $from_ad; // 用户点击的广告ID
		        $_SESSION['referer'] = stripslashes($site_name); // 用户来源

		        unset($site_name);

		        if (!defined('INGORE_VISIT_STATS'))
		        {
		            visit_stats();
		        }
		    }

		    if (empty($_SESSION['user_id']))
		    {
		        if ($this->user->get_cookie())
		        {
		            /* 如果会员已经登录并且还没有获得会员的帐户余额、积分以及优惠券 */
		            if ($_SESSION['user_id'] > 0)
		            {
		                update_user_info();
		            }
		        }
		        else
		        {
		            $_SESSION['user_id']     = 0;
		            $_SESSION['user_name']   = '';
		            $_SESSION['email']       = '';
		            $_SESSION['user_rank']   = 0;
		            $_SESSION['discount']    = 1.00;
		            if (!isset($_SESSION['login_fail']))
		            {
		                $_SESSION['login_fail'] = 0;
		            }
		        }
		    }

		    /* 设置推荐会员 */
		    if (isset($_GET['u']))
		    {
		        set_affiliate();
		    }

		    /* session 不存在，检查cookie */
		    if (!empty($_COOKIE['ECS']['user_id']) && !empty($_COOKIE['ECS']['password']))
		    {
		        // 找到了cookie, 验证cookie信息
		        $sql = 'SELECT user_id, user_name, password ' .
		                ' FROM ' .$this->ecs->table('users') .
		                " WHERE user_id = '" . intval($_COOKIE['ECS']['user_id']) . "' AND password = '" .$_COOKIE['ECS']['password']. "'";

		        $row = $this->db->GetRow($sql);

		        if (!$row)
		        {
		            // 没有找到这个记录
		           $time = time() - 3600;
		           setcookie("ECS[user_id]",  '', $time, '/');
		           setcookie("ECS[password]", '', $time, '/');
		        }
		        else
		        {
		            $_SESSION['user_id'] = $row['user_id'];
		            $_SESSION['user_name'] = $row['user_name'];
		            update_user_info();
		        }
		    }

		    if (isset($this->tpl))
		    {
		        $this->tpl->assign('ecs_session', $_SESSION);
		    }
		}

		/* 判断是否支持 Gzip 模式 */
		if (!defined('INIT_NO_SMARTY') && gzip_enabled())
		{
		    ob_start('ob_gzhandler');
		}
		else
		{
		    ob_start();
		}
	}

	public function assign($tpl_var, $value = ''){
		return $this->tpl->assign($tpl_var, $value);
	}

	public function display($filename, $cache_id = ''){
		return $this->tpl->display($filename, $cache_id);
	}

	public function fetch($filename, $cache_id = ''){
		return $this->tpl->fetch($filename, $cache_id);
	}
	
	public function is_cached($filename, $cache_id = ''){
		return $this->tpl->is_cached($filename, $cache_id);
	}

}