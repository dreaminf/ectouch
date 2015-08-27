<?php
$username = trim($_POST['manager']);
$password = trim($_POST['manager_pwd']);
//网站名称
$site_name = addslashes(trim($_POST['sitename']));
//网站域名
$site_url = trim($_POST['siteurl']);
//描述
$seo_description = trim($_POST['sitedescription']);
//关键词
$seo_keywords = trim($_POST['sitekeywords']);
//更新配置信息
mysql_query("UPDATE `{$dbPrefix}shop_config` SET  `value` = '$site_name' WHERE code='shop_name'");
mysql_query("UPDATE `{$dbPrefix}shop_config` SET  `value` = '$site_name' WHERE code='shop_title' ");
mysql_query("UPDATE `{$dbPrefix}shop_config` SET  `value` = '$seo_description' WHERE code='shop_desc'");
mysql_query("UPDATE `{$dbPrefix}shop_config` SET  `value` = '$seo_keywords' WHERE code='shop_keywords'");

//读取配置文件，并替换真实配置数据
$strConfig = file_get_contents('./' . $config['dbSetFile']);
$strConfig = str_replace('#DB_HOST#', $dbHost, $strConfig);
$strConfig = str_replace('#DB_NAME#', $dbName, $strConfig);
$strConfig = str_replace('#DB_USER#', $dbUser, $strConfig);
$strConfig = str_replace('#DB_PWD#', $dbPwd, $strConfig);
$strConfig = str_replace('#DB_PORT#', $dbPort, $strConfig);
$strConfig = str_replace('#DB_PREFIX#', $dbPrefix, $strConfig);
$strConfig = str_replace('#HASH_CODE#', genRandomString(18), $strConfig);
$strConfig = str_replace('#COOKIE_PREFIX#', genRandomString(6) . "_", $strConfig);
@file_put_contents($config['dbConfig'], $strConfig);

//插入管理员
//生成随机认证码
$verify = genRandomString(6);
$time = time();
$ip = get_client_ip();
$password = md5(md5($password).$verify);
$email = trim($_POST['manager_email']);
$query = "INSERT INTO `{$dbPrefix}admin_user` (user_name, password, ec_salt, email, add_time, last_ip, action_list) VALUES ('{$username}', '{$password}', '{$verify}', '{$email}', '{$time}', '{$ip}', 'all')";
if(mysql_query($query)){
	return array('status'=>2,'info'=>'成功添加管理员<br />成功写入配置文件<br>安装完成...');
}
return array('status'=>0,'info'=>'安装失败...');
