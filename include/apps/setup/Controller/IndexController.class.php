<?php
namespace setup\Controller;

use Think\Controller;

class IndexController extends Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->checkEnv();
    }

    public function indexAction()
    {
        // 安装步骤
        $steps = array(
            '1' => '安装许可协议',
            '2' => '运行环境检测',
            '3' => '安装参数设置',
            '4' => '安装详细过程',
            '5' => '安装完成'
        );
        $step = isset($_GET['step']) ? $_GET['step'] : 1;
        // 当前安装步骤
        $step_html = '';
        foreach ($steps as $key => $value) {
            $current = $key == $step ? 'current' : '';
            $step_html .= '<li class="' . $current . '"><em>' . $key . '</em>' . $value . '</li>';
        }
        $this->assign('step_html', $step_html);
        // 安装页面
        switch ($step) {
            // 安装许可协议
            case '1':
                $this->step1();
                break;
            // 运行环境检测
            case '2':
                $this->step2();
                break;
            // 安装参数设置
            case '3':
                $this->step3();
                break;
            // 安装详细过程
            case '4':
                $this->step4();
                break;
            // 安装完成
            case '5':
                $this->step5();
                break;
        }
    }

    /**
     * 环境检测
     */
    private function checkEnv()
    {
        // 检测是否已经安装
        if (file_exists(C('installFile'))) {
            exit(get_tip_html(C('alreadyInstallInfo')));
        }
        // 检测数据库文件
        foreach (C('sqlFileName') as $sqlFile) {
            if (! file_exists(MODULE_PATH . 'Data/' . $sqlFile)) {
                exit(get_tip_html('数据库文件不存在，无法继续安装！'));
            }
        }
    }

    /**
     * 安装许可协议
     */
    private function step1()
    {
        $license = file_get_contents(MODULE_PATH . 'license.txt');
        $this->assign('license', $license);
        $this->display('install_step1');
    }

    /**
     * 运行环境检测
     */
    private function step2()
    {
        $server = array(
            // 操作系统
            'os' => php_uname(),
            // PHP版本
            'php' => PHP_VERSION
        );
        $error = 0;
        // 数据库
        if (function_exists('mysql_connect')) {
            $server['mysql'] = '<span class="correct_span">&radic;</span> 已安装';
        } else {
            $server['mysql'] = '<span class="correct_span error_span">&radic;</span> 出现错误';
            $error ++;
        }
        // 上传限制
        if (ini_get('file_uploads')) {
            $server['uploadSize'] = '<span class="correct_span">&radic;</span> ' . ini_get('upload_max_filesize');
        } else {
            $server['uploadSize'] = '<span class="correct_span error_span">&radic;</span>禁止上传';
        }
        // session
        if (function_exists('session_start')) {
            $server['session'] = '<span class="correct_span">&radic;</span> 支持';
        } else {
            $server['session'] = '<span class="correct_span error_span">&radic;</span> 不支持';
            $error ++;
        }
        // curl
        if (function_exists('curl_init')) {
            $server['curl'] = '<span class="correct_span">&radic;</span> 支持';
        } else {
            $server['curl'] = '<span class="correct_span error_span">&radic;</span> 不支持';
            $error ++;
        }
        // 需要读写权限的目录
        $folder = C('dirAccess');
        $this->assign('server', $server);
        $this->assign('folder', $folder);
        $this->display('install_step2');
        $_SESSION['INSTALLSTATUS'] = $error == 0 ? 'SUCCESS' : $error;
    }

    /**
     * 安装参数设置
     */
    private function step3()
    {
        verify(3);
        // 测试数据库链接
        if (isset($_GET['testdbpwd'])) {
            empty($_POST['dbhost']) ? alert(0, '数据库服务器地址不能为空！', 'dbhost') : '';
            empty($_POST['dbuser']) ? alert(0, '数据库用户名不能为空！', 'dbuser') : '';
            empty($_POST['dbname']) ? alert(0, '数据库名不能为空！', 'dbname') : '';
            empty($_POST['dbport']) ? alert(0, '数据库端口不能为空！', 'dbport') : '';
            $dbHost = $_POST['dbhost'] . ':' . $_POST['dbport'];
            $conn = mysql_connect($dbHost, $_POST['dbuser'], $_POST['dbpw']);
            $conn ? alert(1, '数据库链接成功！', 'dbpw') : alert(0, '数据库链接失败！', 'dbpw');
        }
        // 自动读取PC端数据库连接信息
        $config = require ROOT_PATH . 'data/config.php';
        $this->assign('config', $config);
        $this->display('install_step3');
    }

    /**
     * 安装详细过程
     */
    private function step4()
    {
        verify(4);
        if (intval($_GET['install'])) {
            dataVerify();
            // 设置时区
            date_default_timezone_set('PRC');
            // 当前进行的数据库操作
            $n = intval($_GET['n']);
            $arr = array();
            // 数据库服务器地址
            $dbHost = trim($_POST['dbhost']);
            // 数据库端口
            $dbPort = trim($_POST['dbport']);
            // 数据库名
            $dbName = trim($_POST['dbname']);
            $dbHost = empty($dbPort) || $dbPort == 3306 ? $dbHost : $dbHost . ':' . $dbPort;
            // 数据库用户名
            $dbUser = trim($_POST['dbuser']);
            // 数据库密码
            $dbPwd = trim($_POST['dbpw']);
            // 表前缀
            $dbPrefix = empty($_POST['dbprefix']) ? 'db_' : trim($_POST['dbprefix']);
            // 链接数据库
            $conn = mysql_connect($dbHost, $dbUser, $dbPwd);
            if (! $conn) {
                alert(0, '连接数据库失败!');
            }
            // 设置数据库编码
            mysql_query("SET NAMES 'utf8'"); // ,character_set_client=binary,sql_mode='';
                                             // 获取数据库版本信息
            $version = mysql_get_server_info($conn);
            if ($version < 5.0) {
                alert(0, '数据库版本太低!');
            }
            // 选择数据库
            if (! mysql_select_db($dbName, $conn)) {
                // 创建数据时同时设置编码
                if (! mysql_query("CREATE DATABASE IF NOT EXISTS `" . $dbName . "` DEFAULT CHARACTER SET utf8;", $conn)) {
                    alert(0, '<li><span class="correct_span error_span">&radic;</span>数据库 ' . $dbName . ' 不存在，也没权限创建新的数据库！<span style="float: right;">' . date('Y-m-d H:i:s') . '</span></li>');
                } else {
                    alert(1, "<li><span class='correct_span'>&radic;</span>成功创建数据库:{$dbName}<span style='float: right;''>" . date('Y-m-d H:i:s') . "</span></li>", 0);
                }
            }
            // 读取数据文件
            foreach (C('sqlFileName') as $sqlFile) {
                $sqldata .= file_get_contents(MODULE_PATH . 'Data/' . $sqlFile);
            }
            if (empty($sqldata)) {
                alert(0, '数据库文件不能为空！');
            }
            // 获得默认主题名称
            $result = mysql_query('select `value` from ' . $dbPrefix . 'shop_config where `code` = "template"', $conn);
            if ($result) {
                $row = mysql_fetch_assoc($result);
                $newThemes = $row['value'];
                $sqldata = str_replace('/default/', '/' . $newThemes . '/', $sqldata);
                $oldThemes = '../template/default';
                if (is_dir($oldThemes)) {
                    rename($oldThemes, '../template/' . $newThemes);
                }
            }
            $sqlFormat = sql_split($sqldata, $dbPrefix, C('dbPrefix'));
            
            /**
             * 执行SQL语句
             */
            $counts = count($sqlFormat);
            for ($i = $n; $i < $counts; $i ++) {
                $sql = trim($sqlFormat[$i]);
                if (strstr($sql, 'CREATE TABLE')) {
                    // 创建表
                    preg_match('/CREATE TABLE `([^ ]*)`/', $sql, $matches);
                    if (empty($matches)) {
                        preg_match('/CREATE TABLE IF NOT EXISTS `([^ ]*)`/', $sql, $matches);
                    }
                    if (! empty($matches[1])) {
                        mysql_query("DROP TABLE IF EXISTS `$matches[1]", $conn);
                        $ret = mysql_query($sql, $conn);
                        $i ++;
                        if (mysql_query($sql, $conn)) {
                            $info = '<li><span class="correct_span">&radic;</span>创建数据表' . $matches[1] . '，完成！<span style="float: right;">' . date('Y-m-d H:i:s') . '</span></li> ';
                            alert(1, $info, $i);
                        } else {
                            $info = '<li><span class="correct_span error_span">&radic;</span>创建数据表' . $matches[1] . '，失败，安装停止！<span style="float: right;">' . date('Y-m-d H:i:s') . '</span></li>';
                            alert(0, $info, $i);
                        }
                    }
                } else {
                    // 插入数据
                    $ret = mysql_query($sql);
                }
            }
            
            // 处理
            $data = $this->handler();
            $_SESSION['INSTALLOK'] = $data['status'] ? 1 : 0;
            alert($data['status'], $data['info']);
        }
        $this->display('install_step4');
    }

    /**
     * 安装完成
     */
    private function step5()
    {
        verify(5);
        $this->sync();
        $this->display('install_step5');
    }
    
    private function sync(){
        // 安装完成,生成.lock文件
        if (isset($_SESSION['INSTALLOK']) && $_SESSION['INSTALLOK'] == 1) {
            //             $this->filewrite(C('installFile'));
        }
        $appid = md5(U('/', '', true, true));
        $appkey = ROOT_PATH . 'data/certificate/appid.php';
        $content = "<?php\ndefine('APP_ID', '" . $appid . "');";
        vendor('library.Http');
        vendor('library.Cloud');
        @file_put_contents($appkey, $content);
        $cloud = \Cloud::getInstance();
        $site_info = D('Index')->getSiteInfo($appid);
        $cloud->data($site_info)->act('post.install');
        unset($_SESSION);
    }
    
    // 写入文件
    private function filewrite($file)
    {
        @touch($file);
    }

    private function handler()
    {
        // 网站域名
        $site_url = U('/', '', true, true);
        // 插入微信菜单
        $query = "INSERT INTO `{$dbPrefix}wechat_menu` (`id`, `wechat_id`, `pid`, `name`, `type`, `key`, `url`, `sort`, `status`) VALUES
(1, 1, 0, '微商城', 'view', '', '{$site_url}', 1, 1),
(2, 1, 0, '我的订单', 'view', '', '{$site_url}?index.php?r=user/order', 2, 1),
(3, 1, 0, '个人中心', 'view', '', '{$site_url}?index.php?r=user', 3, 1)";
        mysql_query($query);
        
        // 读取配置文件，并替换真实配置数据
        $strConfig = file_get_contents(MODULE_PATH . 'Conf/' . $config['dbSetFile']);
        $strConfig = str_replace('#DB_HOST#', $dbHost, $strConfig);
        $strConfig = str_replace('#DB_NAME#', $dbName, $strConfig);
        $strConfig = str_replace('#DB_USER#', $dbUser, $strConfig);
        $strConfig = str_replace('#DB_PWD#', $dbPwd, $strConfig);
        $strConfig = str_replace('#DB_PORT#', $dbPort, $strConfig);
        $strConfig = str_replace('#DB_PREFIX#', $dbPrefix, $strConfig);
        @file_put_contents(C('dbConfig'), $strConfig);
        
        return array(
            'status' => 2,
            'info' => '成功写入配置文件<br>安装完成...'
        );
    }
}