<?php

/**
 * 错误提示html
 * @param unknown $info
 * @return string
 */
function get_tip_html($info)
{
    return '<div style="border: 2px solid #69c; background:#f1f1f1; padding:20px;margin:20px;width:800px;font-weight:bold;color: #69c;text-align:center;margin-left: auto;margin-right: auto;border-radius: 5px;"><h1>' . $info . '</h1></div>';
}

/**
 * 返回提示信息
 *
 * @param unknown $status            
 * @param unknown $info            
 * @param number $type            
 */
function alert($status, $info, $type = 0)
{
    exit(json_encode(array(
        'status' => $status,
        'info' => $info,
        'type' => $type
    )));
}

/**
 * 安装校验
 *
 * @param number $step            
 */
function verify($step = 3)
{
    if ($step >= 3) {
        // 未运行环境检测，跳转到安装许可协议页面
        if (! isset($_SESSION['INSTALLSTATUS'])) {
            header('location:./index.php');
            exit();
        }
        // 运行环境检测存在错误，返回运行环境检测
        if ($_SESSION['INSTALLSTATUS'] != 'SUCCESS') {
            header('location:./index.php?step=2');
            exit();
        }
    }
    if ($step == 4) {
        // 未提交数据
        if (empty($_POST)) {
            header('location:./index.php?step=3');
            exit();
        }
    }
    if ($step >= 5) {
        // 数据库未写入完成
        if (! isset($_SESSION['INSTALLOK'])) {
            header('location:./index.php?step=4');
            exit();
        }
    }
}

/**
 * 数据校验
 *
 * @param number $independent            
 */
function dataVerify()
{
    empty($_POST['dbhost']) ? alert(0, '数据库服务器不能为空！') : '';
    empty($_POST['dbport']) ? alert(0, '数据库端口不能为空！') : '';
    empty($_POST['dbuser']) ? alert(0, '数据库用户名不能为空！') : '';
    empty($_POST['dbname']) ? alert(0, '数据库名不能为空！') : '';
    empty($_POST['dbprefix']) ? alert(0, '数据库表前缀不能为空！') : '';
}

/**
 * 判断目录是否可写
 *
 * @param unknown $d            
 * @return boolean
 */
function testwrite($d)
{
    $tfile = "_test.txt";
    $fp = fopen(ROOT_PATH . $d . "/" . $tfile, "w");
    if (! $fp) {
        return false;
    }
    fclose($fp);
    $rs = unlink(ROOT_PATH . $d . "/" . $tfile);
    if ($rs) {
        return true;
    }
    return false;
}

/**
 * 创建目录
 *
 * @param unknown $path
 * @param number $mode
 * @return boolean
 */
function dir_create($path, $mode = 0777)
{
    if (is_dir($path)) {
        return true;
    }
    mkdir($path, $mode, true);
    chmod($path, $mode);
}

/**
 * 数据库语句解析
 *
 * @param unknown $sql            
 * @param unknown $newTablePre            
 * @param unknown $oldTablePre            
 * @return string[]|unknown[]
 */
function sql_split($sql, $newTablePre, $oldTablePre)
{
    // 前缀替换
    if ($newTablePre != $oldTablePre) {
        $sql = str_replace($oldTablePre, $newTablePre, $sql);
    }
    $sql = preg_replace("/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=utf8", $sql);
    
    $sql = str_replace("\r", "\n", $sql);
    $ret = array();
    $queriesarray = explode(";\n", trim($sql));
    unset($sql);
    foreach ($queriesarray as $k => $query) {
        $ret[$k] = '';
        $queries = explode("\n", trim($query));
        $queries = array_filter($queries);
        foreach ($queries as $query) {
            $str1 = substr($query, 0, 1);
            if ($str1 != '#' && $str1 != '-')
                $ret[$k] .= $query;
        }
    }
    return $ret;
}

/**
 * 产生随机字符串
 * 产生一个指定长度的随机字符串,并返回给用户
 *
 * @access public
 * @param int $len
 *            产生字符串的位数
 * @return string
 */
function genRandomString($len = 6)
{
    $chars = array(
        "a",
        "b",
        "c",
        "d",
        "e",
        "f",
        "g",
        "h",
        "i",
        "j",
        "k",
        "l",
        "m",
        "n",
        "o",
        "p",
        "q",
        "r",
        "s",
        "t",
        "u",
        "v",
        "w",
        "x",
        "y",
        "z",
        "A",
        "B",
        "C",
        "D",
        "E",
        "F",
        "G",
        "H",
        "I",
        "J",
        "K",
        "L",
        "M",
        "N",
        "O",
        "P",
        "Q",
        "R",
        "S",
        "T",
        "U",
        "V",
        "W",
        "X",
        "Y",
        "Z",
        "0",
        "1",
        "2",
        "3",
        "4",
        "5",
        "6",
        "7",
        "8",
        "9",
        '!',
        '@',
        '#',
        '$',
        '%',
        '^',
        '&',
        '*',
        '(',
        ')'
    );
    $charsLen = count($chars) - 1;
    shuffle($chars); // 将数组打乱
    $output = "";
    for ($i = 0; $i < $len; $i ++) {
        $output .= $chars[mt_rand(0, $charsLen)];
    }
    return $output;
}

/**
 * 生成为一的appid
 */
function get_appid()
{
    if (function_exists('com_create_guid')) {
        $guid = com_create_guid();
    } else {
        mt_srand((double) microtime() * 10000);
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45); // "-"
        $guid = substr($charid, 0, 8) . $hyphen . substr($charid, 8, 4) . $hyphen . substr($charid, 12, 4) . $hyphen . substr($charid, 16, 4) . $hyphen . substr($charid, 20, 12);
    }
    return strtoupper(hash('ripemd128', $guid));
}

