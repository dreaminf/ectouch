<?php

namespace app\console;

use yii\console\Controller;
use yii\httpclient\Client;

class ChaoshiController extends Controller
{

    private $http;

    private $allCategoryUrl = 'https://h5api.m.tmall.com/h5/com.taobao.tmallsearch.service.tmallnavservice/1.0/?jsv=2.4.8&appKey=12574478&t=1512700731710&sign=d7df7f66a29e289b1ed9d85efb1f41dc&api=com.taobao.tmallsearch.service.TmallNavService&v=1.0&type=jsonp&dataType=jsonp&callback=mtopjsonp1&data=%7B%22ver%22%3A1%2C%22rootName%22%3A%22chaoshi%22%7D';

    private $childCategoryUrl = 'https://h5api.m.tmall.com/h5/com.taobao.tmallsearch.service.tmallnavservice/1.0/?jsv=2.4.8&appKey=12574478&t=1512702305029&sign=a8008b28cdfeecd3bd5a0b681de96b36&api=com.taobao.tmallsearch.service.TmallNavService&v=1.0&type=jsonp&dataType=jsonp&callback=mtopjsonp3&';

    public function actionIndex()
    {
        $this->http = new Client();

        $category = $this->getAllCategory();

        foreach ($category as $item) {
            $this->getCategoryDetail($item);
        }

    }

    private function getAllCategory()
    {
        $res = $this->http->get($this->allCategoryUrl);

        dd($res);
    }

    private function getCategoryDetail($id)
    {
        $res = $this->http->get($this->childCategoryUrl . 'data=%7B%22ver%22%3A1%2C%22rootName%22%3A%22chaoshi%22%2C%22catId%22%3A%22' . $id . '%22%7');

        return $res;
    }
}