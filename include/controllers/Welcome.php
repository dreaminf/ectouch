<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends IndexController {

	public function index()
	{
		$this->display('index.dwt');
	}

	public function test(){
		echo 'test';
	}
}
