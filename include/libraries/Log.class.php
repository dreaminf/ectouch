<?php
/**
 * Log 静态类
 */
class Log
{
	public static function info($info){
		$info = is_array($info) ? var_export($info, 1):$info;
		logResult($info);
	}
}