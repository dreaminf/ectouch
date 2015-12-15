<?php

/**
 * Define
 */
defined('ULTRON_DIR') || define('ULTRON_DIR', dirname(__FILE__));

require ULTRON_DIR . '/Config.php';

class Ultron
{
    /**
     * Singleton instance
     *
     * Marked only as protected to allow extension of the class. To extend,
     * simply override {@link getInstance()}.
     *
     * @var Ultron
     */
    protected static $_instance = null;

    /**
     * Object register
     *
     * @var array
     */
    public $reg = array();

    /**
     * Run time config
     *
     * @var Ultron_Config
     */
    public $config;

    /**
     * Router
     *
     * @var Ultron_Router
     */
    public $router;

    /**
     * Path info
     *
     * @var string
     */
    public $pathInfo;

    /**
     * Dispathc info
     *
     * @var array
     */
    public $dispatchInfo;

    /**
     * Constructor
     *
     */
    protected function __construct()
    {
        $this->config = new Ultron_Config(array(
            '_class' => array(
                'Ultron_Model'               => ULTRON_DIR . '/Model.php',
                'Ultron_View'                => ULTRON_DIR . '/View.php',
                'Ultron_Controller'          => ULTRON_DIR . '/Controller.php',
                'Ultron_Router'              => ULTRON_DIR . '/Router.php',
                'Ultron_Request'             => ULTRON_DIR . '/Request.php',
                'Ultron_Response'            => ULTRON_DIR . '/Response.php',
                'Ultron_Ext_Validate'        => ULTRON_DIR . '/Ext/Validate.php',
                'Ultron_Exception'           => ULTRON_DIR . '/Exception.php',
                'Ultron_Exception_Dispatch'  => ULTRON_DIR . '/Exception/Dispatch.php',
            ),
        ));

        Ultron::registerAutoload();
    }

    /**
     * Bootstrap
     *
     * @param mixed $arg string as a file and array as config
     * @return Ultron
     */
    public static function boot($config = 'config.inc.php')
    {
        if (is_string($config) && file_exists($config)) {
            include $config;
        }

        if (!is_array($config)) {
            throw new Exception('Boot config must be an array or a php config file with variable $config');
        }

        self::getInstance()->config->merge($config);
        return self::$_instance;
    }

    /**
     * Singleton instance
     *
     * @return Ultron
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Set Config
     *
     * @param string $name
     * @param mixed $value
     * @param string $delimiter
     * @return Ultron
     */
    public static function setConfig($name, $value, $delimiter = '.')
    {
        self::getInstance()->config->set($name, $value, $delimiter);
        return self::$_instance;
    }

    /**
     * Get Config
     *
     * @return Ultron_Config
     */
    public static function getConfig($name, $default = null, $delimiter = '.')
    {
        return self::getInstance()->config->get($name, $default, $delimiter);
    }

    /**
     * Set Registry
     *
     * @param string $name
     * @param mixed $obj
     * @return Ultron
     */
    public static function setReg($name, $obj)
    {
        self::getInstance()->reg[$name] = $obj;
        return self::$_instance;
    }

    /**
     * Get Registry
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function getReg($name, $default = null)
    {
        $instance = self::getInstance();
        return isset($instance->reg[$name]) ? $instance->reg[$name] : $default;
    }

    /**
     * Common factory pattern constructor
     *
     * @param string $type
     * @param array $config
     * @return Object
     */
    public static function factory($type, $config)
    {
        $adapter = $config['adapter'];
        $class = $type . '_' . ucfirst($adapter);
        return new $class($config);
    }

    /**
     * Load class
     *
     * @param string $className
     * @param string $classFile
     * @return boolean
     */
    public static function loadClass($className, $classFile = '')
    {
        if (class_exists($className, false) || interface_exists($className, false)) {
            return true;
        }

        if ((!$classFile)) {
            $key = "_class.{$className}";
            $classFile = self::getConfig($key);
        }

        /**
         * auto load Ultron class
         */
        if ((!$classFile) && ('Ultron' === substr($className, 0, 4))) {
            $classFile = dirname(Ultron_DIR) . DIRECTORY_SEPARATOR
                       . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        }

        /**
         * auto load controller class
         */
        if ((!$classFile) && ('Controller' === substr($className, -10))) {
            $classFile = self::getConfig('_controllersHome') . "/{$className}.php";
        }

        /**
         * auto load model class
         */
        if ((!$classFile) && ('Model' === substr($className, -5))) {
            $classFile = self::getConfig('_modelsHome') . "/{$className}.php";
        }

        if (file_exists($classFile)) {
            include $classFile;
        }

        return (class_exists($className, false) || interface_exists($className, false));
    }

    /**
     * User define class path
     *
     * @param array $classPath
     * @return Ultron
     */
    public static function setClassPath($class, $path = '')
    {
        if (!is_array($class)) {
            $class = array($class => $path);
        }

        self::getInstance()->config->merge(array('_class' => $class));

        return self::$_instance;
    }

    /**
     * Register autoload function
     *
     * @param string $func
     * @param boolean $enable
     * @return Ultron
     */
    public static function registerAutoload($func = 'Ultron::loadClass', $enable = true)
    {
        $enable ? spl_autoload_register($func) : spl_autoload_unregister($func);
        return self::$_instance;
    }

    /**
     * Get dispatch info
     *
     * @param boolean $init
     * @return array
     */
    public function getDispatchInfo($init = false)
    {
        if ((null === $this->dispatchInfo) && $init) {
            $this->router || ($this->router = new Ultron_Router());

            if ($urls = self::getConfig('_urls')) {
                $this->router->rules += $urls;
            }

            $this->pathInfo || $this->pathInfo = (isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '');

            $this->dispatchInfo = $this->router->match($this->pathInfo);
        }

        return $this->dispatchInfo;
    }

    /**
     * Dispatch
     *
     */
    public function dispatch()
    {
        if (!$dispatchInfo = $this->getDispatchInfo(true)) {
            throw new Ultron_Exception_Dispatch('No dispatch info found');
        }

        if (isset($dispatchInfo['file'])) {
            if (!file_exists($dispatchInfo['file'])) {
                throw new Ultron_Exception_Dispatch("Can't find dispatch file:{$dispatchInfo['file']}");
            }
            require_once $dispatchInfo['file'];
        }

        if (isset($dispatchInfo['controller'])) {
            $classFile = self::getConfig('_controllersHome') . "/{$dispatchInfo['controller']}.php";
            if (!self::loadClass($dispatchInfo['controller'], $classFile)) {
                throw new Ultron_Exception_Dispatch("Can't load controller:{$dispatchInfo['controller']}");
            }
            $controller = new $dispatchInfo['controller']();
        }

        if (isset($dispatchInfo['action'])) {
            $func = isset($controller) ? array($controller, $dispatchInfo['action']) : $dispatchInfo['action'];
            if (!is_callable($func, true)) {
                throw new Ultron_Exception_Dispatch("Can't dispatch action:{$dispatchInfo['action']}");
            }
            call_user_func($func);
        }
    }
}