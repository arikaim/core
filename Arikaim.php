<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core;

use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Routing\RouteContext;

use Arikaim\Container\Container;
use Arikaim\Core\Validator\ValidatorStrategy;
use Arikaim\Core\Collection\Arrays;
use Arikaim\Core\App\ServiceContainer;
use Arikaim\Core\App\Routes;
use Arikaim\Core\Collection\Interfaces\CollectionInterface;
use Arikaim\Core\App\Path;
use Arikaim\Core\Middleware\MiddlewareManager;
use Arikaim\Core\System\Error\ApplicationError;
use Arikaim\Core\Http\Session;
use Arikaim\Core\Utils\Number;
use Arikaim\Core\Utils\DateTime;

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
    private static $basePath;

    /**
     * Start time
     *
     * @var integer
     */
    private static $startTime;

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
                return (isset($service[$name]) == true) ? Arrays::getValue($service[$name],$key) : Arrays::getValue($service,$key);                            
            }            
            if (is_object($service) == true) {
                if ($service instanceof CollectionInterface) {
                    return Arrays::getValue($service->toArray(),$key);                  
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
     * @param boolean $loadRoutes - load routes 
     * @return void
    */
    public static function init($loadRoutes = true) 
    {        
        // set start time
        Self::$startTime = microtime(true);

        ini_set('display_errors',1);
        ini_set('display_startup_errors',1);
        error_reporting(E_ALL); 

        set_error_handler(function () {
            return Self::end();
        });

        Self::resolveEnvironment($_SERVER);

        // init constants
        define('ARIKAIM_VERSION','1.0.36');
       
        if (defined('ARIKAIM_ROOT_PATH') == false) {
            define('ARIKAIM_ROOT_PATH',Self::getRootPath());
        }
        define('ARIKAIM_BASE_PATH',Self::getBasePath());
        define('ARIKAIM_PATH',ARIKAIM_ROOT_PATH . ARIKAIM_BASE_PATH . DIRECTORY_SEPARATOR . 'arikaim');  
        define('ARIKAIM_DOMAIN',Self::getDomain());

        $loader = new \Arikaim\Core\System\ClassLoader(ARIKAIM_BASE_PATH,ARIKAIM_ROOT_PATH,'Arikaim\Core',[
            'Arikaim\Extensions',
            'Arikaim\Modules'
        ]);
        $loader->register();
        
        // load global functions
        $loader->LoadClassFile('\\Arikaim\\Core\\App\\Globals');
         
        register_shutdown_function("\Arikaim\Core\Arikaim::end");
        
        if (Arikaim::isConsole() == false) {
            Session::start();
        }

        // create service container            
        AppFactory::setContainer(ServiceContainer::init(new Container()));
    
        // create app 
        Self::$app = AppFactory::create();
        Self::$app->setBasePath(ARIKAIM_BASE_PATH);
      
        // add default middleware
        MiddlewareManager::init();

        // set router 
        Self::$app->getRouteCollector()->setDefaultInvocationStrategy(new ValidatorStrategy());
        Self::$app->getRouteCollector()->setCacheFile(Path::CACHE_PATH . "/routes.cache.php");

        if ($loadRoutes == true) {
            // map routes                       
            Routes::mapSystemRoutes();    
            Routes::mapRoutes();          
        }      
        
        // DatTime and numbers format
        Number::setFormats(Self::options()->get('number.format.items'));
        DateTime::setTimeZone(Arikaim::options()->get('time.zone'));
        DateTime::setFormats(Arikaim::options()->get('date.format.items'),Arikaim::options()->get('time.format.items'));      
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
     * Create request
     *
     * @return ServerRequestInterface
     */
    public static function createRequest()
    {
        $serverRequestCreator = ServerRequestCreatorFactory::create();
        $request = $serverRequestCreator->createServerRequestFromGlobals();

        return $request;
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
        try {
            Self::init();    
            Self::$app->run();  
        } catch (\Exception $exception) {               
            ApplicationError::render($exception,Arikaim::createRequest());          
        }        
    }
    
    /**
     * Get current route from request
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return Route|null
     */
    public static function getCurrentRoute($request)
    {
        $routeContext = RouteContext::fromRequest($request);
        
        return $routeContext->getRoute();
    }

    /**
     * Force garbage collector before exit
     *
     * @return void
     */
    public static function end() 
    {    
        if (error_reporting() == true) {
            $error = error_get_last();                
            if (empty($error) == false) {
                ApplicationError::render($error,Arikaim::createRequest());          
            }          
        }
    }

    /**
     * Return error message
     *
     * @param string $errorCode Error code
     * @param array $params Erorr params
     * @param string|null $default
     * @return string
    */
    public static function getError($errorCode, array $params = [], $default = 'UNKNOWN_ERROR') 
    {
        return Self::errors()->getError($errorCode,$params, $default);
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
            $path = rtrim(str_ireplace('index.php','',Self::$basePath), DIRECTORY_SEPARATOR);
            return ($path == "/") ? "" : $path;               
        } 
        
        return Self::getConsoleBasePath();
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
        return (microtime(true) - Self::$startTime);
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
        $secure = (isset($env['HTTPS']) == true) ? $env['HTTPS'] : null;
        Self::$scheme = (empty($secure) || $secure === 'off') ? 'http' : 'https';

        // host
        $serverName = (isset($env['SERVER_NAME']) == true) ? $env['SERVER_NAME'] : '';            
        $host = (isset($env['HTTP_HOST']) == true) ? $env['HTTP_HOST'] : $serverName;   

        if (preg_match('/^(\[[a-fA-F0-9:.]+\])(:\d+)?\z/',$host,$matches) == false) {           
            $host = (strpos($host,':') !== false) ? strstr($host,':', true) : $host;                             
        } 
        Self::$host = $host;

        // path
        $scriptName = (string)parse_url($env['SCRIPT_NAME'],PHP_URL_PATH);
        $scriptDir = dirname($scriptName);      
        $uri = (isset($env['REQUEST_URI']) == true) ? $env['REQUEST_URI'] : '';  
        $uri = (string)parse_url(Self::getDomain() . $uri,PHP_URL_PATH);
        
        if (stripos($uri,$scriptName) === 0) {
            Self::$basePath = $scriptName;
        } elseif ($scriptDir !== '/' && stripos($uri, $scriptDir) === 0) {
            Self::$basePath = $scriptDir;
        }       
    }

    /**
     * Return composer core package name
     *
     * @return string
     */
    public static function getCorePackageName()
    {
        return "arikaim/core";
    }
}
