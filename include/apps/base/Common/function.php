<?php

/**
 * 获取站点url
 */
function site_url($url = ''){
	$domain = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
	$url = empty($url) ? '/': $url . '/';
	return $domain . __ROOT__ . $url;
}