<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Router extends CI_Router
{
	/**
	 * Set directory name
	 *
	 * @param	string	$dir	Directory name
	 * @param	bool	$append	Whether we're appending rather than setting the full value
	 * @return	void
	 */
	public function set_directory($dir, $append = FALSE)
	{
		$_d = $this->config->item('directory_trigger');
		$_d = isset($_GET[$_d]) ? trim($_GET[$_d], " \t\n\r\0\x0B/") : '';
		$dir = ($_d !== '') ? $_d : $dir;
		if ($append !== TRUE OR empty($this->directory))
		{
			$this->directory = str_replace('.', '', trim($dir, '/')).'/';
		}
		else
		{
			$this->directory .= str_replace('.', '', trim($dir, '/')).'/';
		}
	}

	/**
	 * Set method name
	 *
	 * @param	string	$method	Method name
	 * @return	void
	 */
	public function set_method($method)
	{
		$_a = $this->config->item('function_trigger');
		$_a = isset($_GET[$_a]) ? trim($_GET[$_a], " \t\n\r\0\x0B/") : '';
		$method = ($_a !== '') ? $_a : $method;
		$this->method = $method;
	}
}