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

use Arikaim\Core\Utils\Arrays;
use Arikaim\Core\ClassLoader;
use Arikaim\Core\System\System;
use Arikaim\Core\System\ServiceContainer;
use Arikaim\Core\System\Routes;
use Arikaim\Core\Interfaces\CollectionInterface;

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
     * Register Arikaim class loader.
     *
     * @return void
    */
    public static function registerLoader()
    {
        Self::$uri = Uri::createFromEnvironment(new Environment($_SERVER));
        $loader = new \Arikaim\Core\System\ClassLoader(Self::getBasePath(),Self::getRootPath());
        $loader->register();
        
        // load global functions
        $loader->LoadClassFile('\\Arikaim\\Core\\System\\Globals');
    }

    /**
     * Initialize Arikaim system. Create container services, load system routes 
     *
     * @return void
    */
    public static function create() 
    {        
        Self::registerLoader();
      
        // set start time
        System::initStartTime();
        
        register_shutdown_function("\Arikaim\Core\Arikaim::end");

        // create service container
        $service_container = new ServiceContainer();
        Self::$container = $service_container->getContainer(); 
        $service_container->boot();

        Self::$container['settings'] = Self::config('settings');
        Self::$app = new \Slim\App(Self::$container);
        // map routes
        Self::$app = Routes::mapSystemRoutes(Self::$app);
    }

    /**
     * Start Arikaim
     *
     * @return void
    */
    public static function run() 
    {
        Self::create();
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
        if (defined('ARIKAIM_PATH') == true) {
            return ARIKAIM_PATH;
        }
        return dirname(dirname(__DIR__));
    }

    public static function getConsoleBasePath()
    {
        return ""; 
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
     * Return Arikaim system path.
     *
     * @return string
    */
    public static function getArikaimPath()
    {
        return Self::getRootPath() . Self::getBasePath() . DIRECTORY_SEPARATOR . 'arikaim'; 
    }

    /**
     * Return core modules path.
     *
     * @return string
    */
    public static function getModulesPath()
    {
        return Self::getArikaimPath() . DIRECTORY_SEPARATOR . 'modules'; 
    }

    /**
     *  Return view path.
     *
     * @return string
    */
    public static function getViewPath() 
    {
        return Self::getArikaimPath() . DIRECTORY_SEPARATOR . 'view';       
    }

    /**
     * Return view url.
     *
     * @return string
    */
    public static function getViewURL() 
    {
        return Self::getBaseUrl() . '/arikaim/view';
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
     * Return base url.
     *
     * @return string
    */
    public static function getBaseUrl() 
    {       
        return Self::getDomain() . Self::getBasePath();       
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
}
