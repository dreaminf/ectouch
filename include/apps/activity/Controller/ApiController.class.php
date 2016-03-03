<?php
namespace activity\Controller;

use base\Controller\RestController;

class ApiController extends RestController
{

	public function indexAction(){
        $page = I('get.page', 1);
		$list = D('Activity')->getList($page);
		$this->response($list, 'json');
	}

}
