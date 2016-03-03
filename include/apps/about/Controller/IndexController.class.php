<?php
namespace about\Controller;

use base\Controller\FrontendController;

class IndexController extends FrontendController
{
    public function mapAction()
    {
        $address = I('get.address', '');
        if(empty($address)){
        	$province = get_region_name(C('SHOP_PROVINCE'));
        	$city = get_region_name(C('SHOP_CITY'));
            $address = C('SHOP_ADDRESS');
        }
        $this->assign('city', $city);
        $this->assign('address', $city . $address);
        $this->display('about_map.dwt');
    }
}