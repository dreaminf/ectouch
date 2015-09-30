<?php

/**
 * 缓存管理类
 */
class Cache {
    public $cache = NULL;
    protected $options = array('CACHE_TYPE' => 'Files');

    public function __construct( $options = array() ) {
        if(!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        $this->init();
    }

    public function init() {
        $cacheDriver = ucfirst( $this->options['CACHE_TYPE'] ) .'Driver';
        require_once( dirname(__FILE__) . '/cache/' . $cacheDriver . '.php' );
        $this->cache = new $cacheDriver( $this->options ); //实例化数据库驱动类
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
        return $this->cache->get($name);
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param int $expire  有效时间 0为永久
     * @return boolen
     */
    public function set($name,$value,$expire=null) {
        return $this->cache->set($name,$value,$expire);
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolen
     */
    public function rm($name) {
        return $this->cache->rm($name);
    }

    /**
     * 清除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolen
     */
    public function clear() {
        return $this->cache->clear();
    }
}