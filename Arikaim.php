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
    private static $app;
    private static $container;
    private static $uri;

    public static function getApp()
    {
        return Self::$app;
    }
    
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
    
    public static function hasModule($name)
    {
        return Self::$container->has($name);
    }

    public static function getContainer()
    {
        return Self::$container;
    }

    public static function registerLoader()
    {
        // init uri 
        Self::$uri = Uri::createFromEnvironment(new Environment($_SERVER));
        $loader = new \Arikaim\Core\System\ClassLoader(Self::getBasePath(),Self::getRootPath());
        $loader->register();
    }

    /**
     * Create 
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
  
    public static function run() 
    {
        Self::create();
        Self::$app->run();
    }
    
    public static function end() 
    {
        gc_collect_cycles();            
        exit(0);
    }

    public static function getError($error_name,$params = []) 
    {
        return Self::errors()->getError($error_name,$params);
    }

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

    public static function getRootPath() 
    {
        if (Self::isConsole() == false) {
            return rtrim(realpath($_SERVER['DOCUMENT_ROOT']),DIRECTORY_SEPARATOR);
        }
        // get root path for console run
        return Self::getConsoleRootPath();
    }

    public static function getBasePath() 
    {        
        if (Self::isConsole() == false) {
            $path = rtrim(str_ireplace('index.php','',Self::$uri->getBasePath()), DIRECTORY_SEPARATOR);
            if ($path == "/") {
                $path = "";
            }
        } else {
            $path = Self::getConsoleBasePath();
        }
        return $path;
    }

    public static function getArikaimPath()
    {
        return Self::getRootPath() . Self::getBasePath() . DIRECTORY_SEPARATOR . 'arikaim'; 
    }

    public static function getModulesPath()
    {
        return Self::getArikaimPath() . DIRECTORY_SEPARATOR . 'modules'; 
    }

    public static function getViewPath() 
    {
        return Self::getRootPath() . Self::getBasePath() . DIRECTORY_SEPARATOR . 'arikaim' . DIRECTORY_SEPARATOR . 'view';       
    }
    
    public static function getViewURL() 
    {
        $path = join('/',array(Self::getBaseUrl(),'arikaim','view'));
        return $path;
    }

    public static function getDomain() 
    {
        $scheme = Self::$uri->getScheme();
        $host = Self::$uri->getHost();
        $domain = $scheme . "://" . $host;
        return $domain;
    }

    public static function getBaseUrl() 
    {       
        return Self::getDomain() . Self::getBasePath();       
    }

    public static function isConsole()
    {
        if (php_sapi_name() == "cli") {
           return true;
        }
        return false;
    }    
}
