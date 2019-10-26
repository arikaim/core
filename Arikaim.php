<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core;

use Http\Factory\Guzzle\StreamFactory;
use Slim\Factory\AppFactory;
use Slim\Middleware\ContentLengthMiddleware;
use Slim\Middleware\OutputBufferingMiddleware;
use Slim\Middleware\BodyParsingMiddleware;

use Arikaim\Container\Container;
use Arikaim\Core\Validator\ValidatorStrategy;
use Arikaim\Core\Utils\Arrays;
use Arikaim\Core\System\ServiceContainer;
use Arikaim\Core\System\Routes;
use Arikaim\Core\Interfaces\Collection\CollectionInterface;
use Arikaim\Core\System\Path;
use Arikaim\Core\System\ModulesMiddleware;

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
    public static $app;
    
    /**
     * Http Scheme
     * 
     * @var string
    */
    private static $scheme;

    /**
     * Host
     * 
     * @var string
    */
    private static $host;

    /**
     * App base path
     *
     * @var string
     */
    private static $base_path;

    /**
     * Start time
     *
     * @var integer
     */
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
        if (Self::$app->getContainer() == null) {
            return null;
        }    
       
        if (Self::$app->getContainer()->has($name) == true) {
            $service = Self::$app->getContainer()->get($name);
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
        return Self::$app->getContainer()->has($name);
    }

    /**
     * Return service container object.
     *
     * @return object
    */
    public static function getContainer()
    {
        return Self::$app->getContainer();
    }

    /**
     * Create Arikaim system. Create container services, load system routes 
     * 
     * @param boolean $load_routes - load routes 
     * @return void
    */
    public static function init($load_routes = true) 
    {        
        // set start time
        Self::$start_time = microtime(true);

        ini_set('display_errors',1);
        ini_set('display_startup_errors',1);
        error_reporting(E_ALL); 

        Self::resolveEnvironment($_SERVER);

        // init constants
        define('ARIKAIM_VERSION','1.0.36');
       
        if (defined('ARIKAIM_ROOT_PATH') == false) {
            define('ARIKAIM_ROOT_PATH',Self::getRootPath());
        }
        define('ARIKAIM_BASE_PATH',Self::getBasePath());
        define('ARIKAIM_PATH',ARIKAIM_ROOT_PATH . ARIKAIM_BASE_PATH . DIRECTORY_SEPARATOR . 'arikaim');  
        define('ARIKAIM_DOMAIN',Self::getDomain());

        $loader = new \Arikaim\Core\System\ClassLoader(ARIKAIM_BASE_PATH,ARIKAIM_ROOT_PATH,'Arikaim' . DIRECTORY_SEPARATOR . 'Core','Arikaim' . DIRECTORY_SEPARATOR . 'Extensions');
        $loader->register();
        
        // load global functions
        $loader->LoadClassFile('\\Arikaim\\Core\\System\\Globals');
         
        register_shutdown_function("\Arikaim\Core\Arikaim::end");
        
        // create service container            
        AppFactory::setContainer(ServiceContainer::init(new Container()));
        // create app 
        Self::$app = AppFactory::create();
        Self::$app->setBasePath(ARIKAIM_BASE_PATH);
        // add default middleware
        Self::$app->addRoutingMiddleware();
        Self::$app->add(new ContentLengthMiddleware());
        Self::$app->add(new BodyParsingMiddleware());
        $error_middleware = Self::$app->addErrorMiddleware(true, true, true);
        $error_middleware->setDefaultErrorHandler(new \Arikaim\Core\System\Error\ApplicationError());

        Self::$app->add(new OutputBufferingMiddleware(new StreamFactory(),OutputBufferingMiddleware::APPEND));
        // sanitize request body and client ip
        Self::$app->add(new \Arikaim\Core\Middleware\CoreMiddleware());        
        // add modules middlewares 
        ModulesMiddleware::add();

        // set router 
        Self::$app->getRouteCollector()->setDefaultInvocationStrategy(new ValidatorStrategy());
        Self::$app->getRouteCollector()->setCacheFile(Path::CACHE_PATH . "/routes.cache.php");

        // load class aliases
        $aliases = Arikaim::config()->load('aliases.php');                   
        $loader->loadAlliases($aliases);
    
        if ($load_routes == true) {
            // map routes                       
            Routes::mapSystemRoutes();    
            Routes::mapRoutes();          
        }       
    }
    
    /**
     * Get route parser
     *
     * @return RouteParser
     */
    public static function getRouteParser()
    {
        return Self::$app->getRouteCollector()->getRouteParser();
    }

    /**
     * Create response object
     *
     * @return ResponseInterface
     */
    public static function response()
    {
        return Self::$app->getResponseFactory()->createResponse();
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
     * @param string|null $default
     * @return string
    */
    public static function getError($error_code,array $params = [], $default = 'UNKNOWN_ERROR') 
    {
        return Self::errors()->getError($error_code,$params, $default);
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

    /**
     * Get console base path
     *
     * @return string
     */
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
            $path = rtrim(str_ireplace('index.php','',Self::$base_path), DIRECTORY_SEPARATOR);
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
        return Self::$scheme . "://" . Self::$host;
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
    
    /**
     * Get script execution time
     *
     * @return integer
     */
    public static function getExecutionTime() 
    {
        return (microtime(true) - Self::$start_time);
    }

    /**
     *  Resolve base path, host, scheme 
     *
     * @param array $env
     *
     * @return self
     */
    public static function resolveEnvironment(array $env)
    {
        // scheme
        $is_secure = (isset($env['HTTPS']) == true) ? $env['HTTPS'] : null;
        Self::$scheme = (empty($is_secure) || $is_secure === 'off') ? 'http' : 'https';
        // host
        $server_name = (isset($env['SERVER_NAME']) == true) ? $env['SERVER_NAME'] : '';            
        $host = (isset($env['HTTP_HOST']) == true) ? $env['HTTP_HOST'] : $server_name;         
        if (preg_match('/^(\[[a-fA-F0-9:.]+\])(:\d+)?\z/', $host, $matches) == false) {           
            $host = (strpos($host,':') !== false) ? strstr($host,':', true) : $host;                             
        } 
        Self::$host = $host;
        // path
        $script_name = (string)parse_url($env['SCRIPT_NAME'], PHP_URL_PATH);
        $script_dir = dirname($script_name);      
        $request_uri = (isset($env['REQUEST_URI']) == true) ? $env['REQUEST_URI'] : '';  
        $request_uri = (string)parse_url('http://example.com' . $request_uri, PHP_URL_PATH);
        
        if (stripos($request_uri, $script_name) === 0) {
            Self::$base_path = $script_name;
        } elseif ($script_dir !== '/' && stripos($request_uri, $script_dir) === 0) {
            Self::$base_path = $script_dir;
        }       
    }
}
