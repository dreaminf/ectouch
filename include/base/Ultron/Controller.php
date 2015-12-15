<?php
/**
 *
 */
abstract class Ultron_Controller
{
    /**
     * Template file extension
     *
     * @var string
     */
    public $tplExt = '.php';

    /**
     * Constructor
     *
     */
    public function __construct() {}

    /**
     * Magic method
     *
     * @param string $methodName
     * @param array $args
     */
    public function __call($method, $args)
    {
        throw new Ultron_Exception("Call to undefined method: Ultron_Controller::{$method}()");
    }

    /**
    * Get var
    *
    * @param string $key
    * @param mixed $default
    */
    protected function get($key = null, $default = null)
    {
        return Ultron_Request::get($key, $default);
    }

    /**
    * Post var
    *
    * @param string $key
    * @param mixed $default
    */
    protected function post($key = null, $default = null)
    {
        return Ultron_Request::post($key, $default);
    }

    /**
    * Param var
    *
    * @param string $key
    * @param mixed $default
    */
    protected function param($key = null, $default = null)
    {
        return Ultron_Request::param($key, $default);
    }

    /**
     * View
     *
     * @param array $config
     * @return Ultron_View
     */
    protected function view($viewsHome = null)
    {
        return $this->view = new Ultron_View($viewsHome);
    }

    /**
     * Display the view
     *
     * @param string $tpl
     */
    protected function display($tpl = null, $dir = null)
    {
        if (empty($tpl)) {
            $tpl = $this->defaultTemplate();
        }

        $this->view->display($tpl, $dir);
    }

    /**
     * Get default template file path
     *
     * @return string
     */
    protected function defaultTemplate()
    {
        $dispatchInfo = Ultron::getInstance()->dispatchInfo;

        $tpl = str_replace('_', DIRECTORY_SEPARATOR, substr($dispatchInfo['controller'], 0, -10))
             . DIRECTORY_SEPARATOR
             . substr($dispatchInfo['action'], 0, -6)
             . $this->tplExt;

        return $tpl;
    }

    /**
     * Redirect to other url
     *
     * @param string $url
     */
    protected function redirect($url, $code = 302)
    {
        $this->response->redirect($url, $code);
    }

    /**
     * Abort
     *
     * @param mixed $data
     * @param string $var jsonp var name
     *
     */
    protected function abort($data, $var = null)
    {
        if (!is_string($data)) {
            $data = json_encode($data);
        }
        echo $var ? "var {$var}={$data};" : $data;
        exit();
    }

    /**
     * Dynamic set vars
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value = null)
    {
        $this->$key = $value;
    }

    /**
     * Dynamic get vars
     *
     * @param string $key
     */
    public function __get($key)
    {
        switch ($key) {
            case 'view':
                $this->view();
                return $this->view;

            case 'request':
                $this->request = new Ultron_Request();
                return $this->request;

            case 'response':
                $this->response = new Ultron_Response();
                return $this->response;

            case 'config':
                $this->config = Ultron::getInstance()->config;
                return $this->config;

            default:
                throw new Ultron_Exception('Undefined property: ' . get_class($this) . '::' . $key);
        }
    }
}
