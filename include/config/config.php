<?php
$config = array(
    'MODULE_DENY_LIST' => array('base'),
    'DEFAULT_MODULE' => 'site',
    'ACTION_SUFFIX' => 'Action',
    'URL_MODEL' => 0,
    'VAR_PATHINFO' => 'r',
    'AUTOLOAD_NAMESPACE' => array(
        'classes' => BASE_PATH . 'classes',
        'libraries' => BASE_PATH . 'libraries',
        'plugins' => ROOT_PATH . 'plugins'
    ),
    'TMPL_PARSE_STRING' => array(
        '__PUBLIC__' => __ROOT__ . '/data/statics',
        '__TPL__' => __ROOT__ . '/data/statics/app/' . I('get.m', '', 'strtolower')
    ),
    'MIGRATE_PASSWORD' => '123456',
    'SHOW_PAGE_TRACE' => true
);
$database = require(ROOT_PATH . 'data/config.php');

return array_merge($config, $database);
