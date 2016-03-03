<?php
$config = array(
	'MODULE_DENY_LIST' => array('common'),
	'DEFAULT_MODULE' => 'site',
	'ACTION_SUFFIX' => 'Action',
	'TMPL_FILE_DEPR' => '_',
	'URL_MODEL' => 0,
	'VAR_PATHINFO' => 'r',
	/*
	'AUTOLOAD_NAMESPACE' => array(
		'classes' => BASE_PATH . 'classes'
	),
	*/
	'TMPL_PARSE_STRING' => array(
     '__PUBLIC__' => __ROOT__ . '/data/assets',
     '__ASSETS__' => __ROOT__ . '/data/assets/app/' . I('get.m', '', 'strtolower')
	),
	
	// 显示页面Trace信息
  'SHOW_PAGE_TRACE' =>true
);
$database = require ROOT_PATH . 'data/config.php';

return array_merge($config, $database);
