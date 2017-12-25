<?php

namespace App\Http\Proxy;

use yii\httpclient\Client;

/**
 * Class ShippingProxy
 * @package App\Http\Proxy
 */
class ShippingProxy
{
    /**
     * @var Http
     */
    private $http;

    /**
     * @var string
     */
    private $queryExpressUrl = 'https://m.kuaidi100.com/query?type=%s&postid=%s';

    /**
     * ShippingProxy constructor.
     */
    public function __construct()
    {
        $this->http = new Client();
    }

    /**
     * @param string $com
     * @param string $num
     * @return mixed
     */
    public function getExpress($com = '', $num = '')
    {
        $url = sprintf($this->queryExpressUrl, $com, $num);
        $cache_id = 'express_' . md5($url);

        $result = S($cache_id);
        if ($result !== false) {
            return $result;
        }

        $respose = $this->http->get($url, 5, $this->defaultHeader());
        $result = json_decode($respose, true);

        if ($result['message'] === 'ok') {
            S($cache_id, $result, 600);
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 默认HTTP头
     *
     * Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.79 Safari/537.36
     * Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1
     *
     * @return string
     */
    private function defaultHeader()
    {
        $header = "User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.%d Safari/537.%d\r\n";
        $header .= "Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n";
        $header .= "Accept-language: zh-cn,zh;q=0.5\r\n";
        $header .= "Accept-Charset: GB2312,utf-8;q=0.7,*;q=0.7\r\n";
        $header = sprintf($header, time(), time() + rand(1000, 9999));
        return $header;
    }

}