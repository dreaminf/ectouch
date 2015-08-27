<?php
$username = trim($_POST['manager']);
$password = trim($_POST['manager_pwd']);
//网站名称
$site_name = addslashes(trim($_POST['sitename']));
//网站域名
$site_url = trim($_POST['siteurl']);
//附件目录
$upload_path = $_SESSION['UPLOADPATH'];
//描述
$seo_description = trim($_POST['sitedescription']);
//关键词
$seo_keywords = trim($_POST['sitekeywords']);
//更新配置信息
mysql_query("UPDATE `{$dbPrefix}config` SET  `value` = '$site_name' WHERE varname='site_name'");
mysql_query("UPDATE `{$dbPrefix}config` SET  `value` = '$site_url' WHERE varname='site_domain' ");
mysql_query("UPDATE `{$dbPrefix}config` SET  `value` = '$seo_description' WHERE varname='site_description'");
mysql_query("UPDATE `{$dbPrefix}config` SET  `value` = '$seo_keywords' WHERE varname='site_keywords'");

if(!empty($upload_path)){
	mysql_query("UPDATE `{$dbPrefix}config` SET  `value` = '$upload_path' WHERE varname='attach_storage_domain' ");
}
if(INSTALLTYPE == 'HOST'){
	//读取配置文件，并替换真实配置数据
	$strConfig = file_get_contents('./' . $config['dbSetFile']);
	$strConfig = str_replace('#DB_HOST#', $dbHost, $strConfig);
	$strConfig = str_replace('#DB_NAME#', $dbName, $strConfig);
	$strConfig = str_replace('#DB_USER#', $dbUser, $strConfig);
	$strConfig = str_replace('#DB_PWD#', $dbPwd, $strConfig);
	$strConfig = str_replace('#DB_PORT#', $dbPort, $strConfig);
	$strConfig = str_replace('#DB_PREFIX#', $dbPrefix, $strConfig);
	$strConfig = str_replace('#AUTHCODE#', genRandomString(18), $strConfig);
	$strConfig = str_replace('#COOKIE_PREFIX#', genRandomString(6) . "_", $strConfig);
	$strConfig = str_replace('#DATA_CACHE_PREFIX#', genRandomString(6) . "_", $strConfig);
	$strConfig = str_replace('#SESSION_PREFIX#', genRandomString(6) . "_", $strConfig);
	@file_put_contents($config['dbConfig'], $strConfig);
}

//插入管理员
//生成随机认证码
$verify = genRandomString(6);
$time = time();
$ip = get_client_ip();
$password = md5($password . md5($verify));
$email = trim($_POST['manager_email']);
$query = "INSERT INTO `{$dbPrefix}member` VALUES (1, 0, 0, '{$username}', '{$password}', '{$email}', '', '', 0, '', '', '{$verify}', 1, '{$time}', 0, 0, 1, 2, 1, '', 65535, 1, 1, 1, 1, 0, '')";
if(mysql_query($query)){
	return array('status'=>2,'info'=>'成功添加管理员<br />成功写入配置文件<br>安装完成...');
}
return array('status'=>0,'info'=>'安装失败...');
