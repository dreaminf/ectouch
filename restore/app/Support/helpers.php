<?php

/**
 * 插件目录
 * @param string $path
 * @return string
 */
function plugin_path($path = '')
{
    return app_path('plugins' . ($path ? DIRECTORY_SEPARATOR . $path : $path));
}

/**
 * 是否为移动设备
 * @return mixed
 */
function is_mobile_device()
{
    $detect = new \Mobile_Detect();
    return $detect->isMobile();
}

/**
 * Get / set the specified session value.
 *
 * @param $name
 * @param string $value
 * @return bool|mixed
 */
function session($name, $value = '')
{
    if (is_null($name)) {
        // 清除
        app('session')->destroy();
    } elseif ('' === $value) {
        // 判断或获取
        return 0 === strpos($name, '?') ? app('session')->has(substr($name, 1)) : app('session')->get($name);
    } elseif (is_null($value)) {
        // 删除
        return app('session')->remove($name);
    } else {
        // 设置
        return app('session')->set($name, $value);
    }
}

/**
 * Cookie管理
 * @param string|array $name cookie名称，如果为数组表示进行cookie设置
 * @param mixed $value cookie值
 * @param mixed $option 参数
 * @return mixed
 */
function cookie($name, $value = '', $option = null)
{
    if (is_null($name)) {
        // 清除指定前缀的所有cookie
        if (empty($_COOKIE)) {
            return;
        }
        foreach ($_COOKIE as $key => $val) {
            setcookie($key, '', $_SERVER['REQUEST_TIME'] - 3600);
            unset($_COOKIE[$key]);
        }
    } elseif ('' === $value) {
        // 获取
        return 0 === strpos($name, '?') ? app('request')->cookies->has(substr($name, 1)) : app('request')->cookies->getValue($name);
    } elseif (is_null($value)) {
        // 删除
        return app('response')->cookies->remove($name);
    } else {
        // 设置
        $options = [
            'name' => $name,
            'value' => $value,
        ];
        if (!is_null($option)) {
            $options['expire'] = local_gettime() + $option * 60;
        }
        return app('response')->cookies->add(new \yii\web\Cookie($options));
    }
}

/**
 * 加载函数库
 * @param array $files
 * @param string $module
 */
function load_helper($files = [], $module = '')
{
    if (!is_array($files)) {
        $files = [$files];
    }
    if (empty($module)) {
        $base_path = app_path('helpers/');
    } else {
        $base_path = app_path('modules/' . ucfirst($module) . '/helpers/');
    }
    foreach ($files as $vo) {
        $helper = $base_path . $vo . '.php';
        if (file_exists($helper)) {
            require_once $helper;
        }
    }
}

/**
 * 加载语言包
 * @param array $files
 * @param string $module
 */
function load_lang($files = [], $module = '')
{
    static $_LANG = [];
    if (!is_array($files)) {
        $files = [$files];
    }
    if (empty($module)) {
        $base_path = resource_path('lang/' . $GLOBALS['_CFG']['lang'] . '/');
    } else {
        $base_path = app_path('modules/' . ucfirst($module) . '/languages/' . $GLOBALS['_CFG']['lang'] . '/');
    }
    foreach ($files as $vo) {
        $helper = $base_path . $vo . '.php';
        $lang = null;
        if (file_exists($helper)) {
            $lang = require_once($helper);
            if (!is_null($lang)) {
                $_LANG = array_merge($_LANG, $lang);
            }
        }
    }
    $GLOBALS['_LANG'] = $_LANG;
}

/**
 * 浏览器友好的变量输出
 * @param mixed $var 变量
 * @param boolean $echo 是否输出 默认为True 如果为false 则返回输出字符串
 * @param string $label 标签 默认为空
 * @param boolean $strict 是否严谨 默认为true
 * @return void|string
 */
function dd($var, $echo = true, $label = null, $strict = true)
{
    $label = ($label === null) ? '' : rtrim($label) . ' ';
    if (!$strict) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        } else {
            $output = $label . print_r($var, true);
        }
    } else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if (!extension_loaded('xdebug')) {
            $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        }
    }
    if ($echo) {
        die($output);
    } else {
        return $output;
    }
}
