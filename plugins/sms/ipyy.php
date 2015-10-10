<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ipyy {

    private $api_urls = array(
        'send' => 'http://sh2.ipyy.com/sms.aspx',
        'query' => 'http://sh2.ipyy.com/statusApi.aspx',
        'overage' => 'http://sh2.ipyy.com/sms.aspx'
    );

    private $config = array(
        'userid' => '',
        'account' => '',
        'password' => '',
    );

    public function __construct($config = array()){
        if(!empty($config)){
            $this->config = array_merge($this->config, $config);
        }
        $this->transport = new transport();
    }

    /**
     * 短信发送接口
     */
    public function send($mobile = '', $content = ''){
        $data = $this->config;
        $data['action'] = 'send';
        $data['mobile'] = $mobile;
        $data['content'] = $content;
        $data['sendTime'] = '';
        $data['extno'] = '';

        $result = $this->response($this->api_urls['send'], $data);
        if($result['returnstatus'] == 'Success'){
            return true;
        }else{
            return $result;
        }
    }

    /**
     * 状态报告接口
     */
    public function query(){
        $data = $this->config;
        $data['action'] = 'query';
        $data['statusNum'] = 100;

        $result = $this->response($this->api_urls['query'], $data);
        if(isset($result['statusbox'])){
            return $result['statusbox'];
        }else{
            return $result['errorstatus'];
        }
    }

    /**
     * 余额及已发送量查询接口
     */
    public function overage(){
        $data = $this->config;
        $data['action'] = 'overage';

        $result = $this->response($this->api_urls['overage'], $data);
        if($result['returnstatus'] == 'Sucess'){
            return $result;
        }else{
            return false;
        }
    }

    private function response($url, $data){
        echo $url;
        echo '<br>';
        dump($data);
        $result_xml = $this->transport->request($url, $data);
        dump($result_xml);
        $result = $this->xml_to_array($result_xml['body']);
        return $result['returnsms'];
    }

    private function xml_to_array($xml) {
        $reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
        if (preg_match_all($reg, $xml, $matches)) {
            $count = count($matches[0]);
            for ($i = 0; $i < $count; $i++) {
                $subxml = $matches[2][$i];
                $key = $matches[1][$i];
                if (preg_match($reg, $subxml)) {
                    $arr[$key] = $this->xml_to_array($subxml);
                } else {
                    $arr[$key] = $subxml;
                }
            }
        }
        return $arr;
    }

}
