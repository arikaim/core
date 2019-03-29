<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core;

use Slim\Http\Uri;
use Slim\Http\Environment;

use Arikaim\Core\FileSystem\File;
use Arikaim\Core\Utils\Arrays;
use Arikaim\Core\ClassLoader;
use Arikaim\Core\System\ServiceContainer;
use Arikaim\Core\System\Routes;
use Arikaim\Core\Interfaces\CollectionInterface;
use Arikaim\Core\System\Config;

/**
 * Arikaim core class
 */
class Arikaim  
{
    /**
     * Slim application object
     * 
     * @var object
    */
    private static $app;
    
    /**
     * Service container 
     * 
     * @var object
    */
    private static $container;

    /**
     * Uri
     * 
     * @var object
    */
    private static $uri;

    /**
     * Get Slim application object
     *
     * @return [object]
    */
    public static function getApp()
    {
        return Self::$app;
    }

    private static $start_time;

    /**
     * Get container service
     *
     * @param string $name Service name
     * @param array $arguments Service params
     * @return mixed service
    */
    public static function __callStatic($name, $arguments)
    {    
        $service = null;
        if (Self::$container == null) {
            return null;
        }    
       
        if (Self::$container->has($name) == true) {
            $service = Self::$container->get($name);
        }
        if (isset($arguments[0]) == true) {
            $key = $arguments[0];
            if (is_array($service) == true) {
                if (isset($service[$name]) == true) {
                    $result = Arrays::getValue($service[$name],$key);
                } else {
                    $result = Arrays::getValue($service,$key);
                } 
                return $result;               
            }            
            if (is_object($service) == true) {
                if ($service instanceof CollectionInterface) {
                    $result = Arrays::getValue($service->toArray(),$key);
                    return $result;
                }
            }            
        }
        return $service;
    }
    
    /**
     * Check if core mudule exists
     *
     * @param string $name Module name.
     * @return boolean
    */
    public static function hasModule($name)
    {
        return Self::$container->has($name);
    }

    /**
     * Return service container object.
     *
     * @return object
    */
    public static function getContainer()
    {
        return Self::$container;
    }

    /**
     *  Create Arikaim system. Create container services, load system routes 
     *  @param boolean load_routes - load routes 
     *  @return void
    */
    public static function init($load_routes = true) 
    {        
        ini_set('display_errors',1);
        ini_set('display_startup_errors',1);
        error_reporting(E_ALL); 

        Self::$uri = Uri::createFromEnvironment(new Environment($_SERVER));

        // init constants
        define('ARIKAIM_VERSION','1.0.0');
        define('ARIKAIM_DOMAIN',Self::getDomain());
        if (defined('ARIKAIM_ROOT_PATH') == false) {
            define('ARIKAIM_ROOT_PATH',Self::getRootPath());
        }
        define('ARIKAIM_BASE_PATH',Self::getBasePath());
        define('ARIKAIM_PATH',ARIKAIM_ROOT_PATH . ARIKAIM_BASE_PATH . DIRECTORY_SEPARATOR . 'arikaim');  
        define('ARIKAIM_CACHE_PATH',ARIKAIM_PATH . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR);

        $loader = new \Arikaim\Core\System\ClassLoader(ARIKAIM_BASE_PATH,ARIKAIM_ROOT_PATH);
        $loader->register();
        
        // error handlers
        //set_exception_handler("\Arikaim\Core\System\Error\ApplicationError::show"); 
       // set_error_handler('\Arikaim\Core\System\Error\PhpError::show',E_ALL);
        
        // load global functions
        $loader->LoadClassFile('\\Arikaim\\Core\\System\\Globals');
        
        // set start time
        Self::initStartTime();        
        register_shutdown_function("\Arikaim\Core\Arikaim::end");
        
        // create service container
        $service_container = new ServiceContainer();
        Self::$container = $service_container->getContainer(); 
        $service_container->boot();
        
        Self::$app = new \Slim\App(Self::$container);
    
        // load class aliases
        $aliases = Config::loadConfig('aliases.php');                   
        $loader->loadAlliases($aliases);
    
        if ($load_routes == true) {
            // map routes              
            Self::$app = Routes::mapSystemRoutes(Self::$app);              
        }
    }
    
    /**
     * Start Arikaim
     *
     * @return void
    */
    public static function run() 
    {
        Self::init();
        Self::$app->run();  
    }
    
    /**
     * Force garbage collector before exit
     *
     * @return void
     */
    public static function end() 
    {
        gc_collect_cycles();            
        exit(0);
    }

    /**
     * Return error message
     *
     * @param string $error_code Error code
     * @param array $params Erorr params
     * @return string
    */
    public static function getError($error_code,array $params = []) 
    {
        return Self::errors()->getError($error_code,$params);
    }

    /**
     * Return console root path.
     *
     * @return string
    */
    public static function getConsoleRootPath()
    {
        return (defined('ARIKAIM_ROOT_PATH') == true) ? ARIKAIM_ROOT_PATH : dirname(dirname(__DIR__));         
    }

    public static function getConsoleBasePath()
    {
        return (defined('ARIKAIM_BASE_PATH') == true) ? ARIKAIM_BASE_PATH : "";       
    }

    /**
     * Return root path.
     *
     * @return string
    */
    public static function getRootPath() 
    {
        if (Self::isConsole() == false) {
            return rtrim(realpath($_SERVER['DOCUMENT_ROOT']),DIRECTORY_SEPARATOR);
        }
        // get root path for console run
        return Self::getConsoleRootPath();
    }

    /**
     * Return base path.
     *
     * @return string
    */
    public static function getBasePath() 
    {        
        if (Self::isConsole() == false) {
            $path = rtrim(str_ireplace('index.php','',Self::$uri->getBasePath()), DIRECTORY_SEPARATOR);
            $path = ($path == "/") ? "" : $path;               
        } else {
            $path = Self::getConsoleBasePath();
        }
        return $path;
    }

    /**
     * Return domain url.
     *
     * @return string
    */
    public static function getDomain() 
    {
        $scheme = Self::$uri->getScheme();
        $host = Self::$uri->getHost();
        $domain = $scheme . "://" . $host;
        return $domain;
    }

    /**
     * Return true if script is run from console.
     *
     * @return boolean
    */
    public static function isConsole()
    {
        return (php_sapi_name() == "cli") ? true : false;         
    }   
    
    public static function initStartTime()
    {
        Self::$start_time = microtime(true);
    }
    
    public static function getStartTime() 
    {
        return Self::$start_time;
    }

    public static function getExecutionTime() 
    {
        return (microtime(true) - Self::$start_time);
    }
}
