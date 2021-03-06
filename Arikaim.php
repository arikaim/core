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

use Psr\Http\Message\ResponseInterface;
use Psr\Container\ContainerInterface;
use Slim\Factory\AppFactory;

use Arikaim\Core\Validator\ValidatorStrategy;
use Arikaim\Core\App\AppContainer;
use Arikaim\Core\Http\Session;
use Arikaim\Core\Middleware\RoutingMiddleware;
use Arikaim\Core\Middleware\ErrorMiddleware;
use Arikaim\Core\Middleware\BodyParsingMiddleware;
use Arikaim\Core\Utils\Path;

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
     * Get container service
     *
     * @param string $name Service name
     * @param array $arguments Service params
     * @return mixed service
    */
    public static function __callStatic($name, $arguments)
    {    
        return Self::get($name);
    }
    
    /**
     * Get item from app container 
     *
     * @param string $name
     * @return mixed
     */
    public static function get(string $name)
    {
        return Self::$app->getContainer()->get($name);
    }

    /**
     * Get item from service container
     *
     * @param string $name
     * @return mixed
     */
    public static function getService(string $name)
    {
        return Self::$app->getContainer()->get('service')->get($name);
    }

    /**
     * Check item exists in container
     *
     * @param string $name Item name.
     * @return boolean
    */
    public static function has(string $name): bool
    {
        return Self::$app->getContainer()->has($name);
    }

    /**
     * Return service container object.
     *
     * @return ContainerInterface
    */
    public static function getContainer()
    {
        return Self::$app->getContainer();
    }

    /**
    * System init
    *
    * @param bool $showErrors
    * @param bool $console
    * @param array|null $config
    * @return void
    */
    public static function systemInit(bool $showErrors = false, bool $console = false, ?array $config = null): void
    {
        \ini_set('display_errors',(int)$showErrors);
        \ini_set('display_startup_errors',(int)$showErrors);
        \error_reporting(($showErrors == true) ? E_ALL : 0); 

        // Init constants     
        (\defined('ROOT_PATH') == false) ? \define('ROOT_PATH',Self::getRootPath($console)) : null;
        \define('DOMAIN',$config['environment']['host'] ?? Self::resolveHost($_SERVER));  
        \define('BASE_PATH',$config['environment']['basePath'] ?? Self::resolveBasePath($_SERVER,DOMAIN));      
        \define('APP_PATH',ROOT_PATH . BASE_PATH . DIRECTORY_SEPARATOR . 'arikaim');       
        \define('APP_URL',DOMAIN . BASE_PATH . '/arikaim');
        \define('CORE_NAMESPACE','Arikaim\\Core');     

        $loader = new \Arikaim\Core\System\ClassLoader(BASE_PATH,ROOT_PATH,'Arikaim\Core',[
            'Arikaim\Extensions',
            'Arikaim\Modules'
        ]);
        $loader->register();
                
        // load config
        $config = $config ?? include (Path::CONFIG_PATH . 'config.php');
        // Datetime zone       
        \date_default_timezone_set($config['settings']['timeZone'] ?? \date_default_timezone_get());
        // Define date, time, number constants  
        \define('CURRENT_NUMBER_FORMAT',$config['settings']['numberFormat'] ?? null);                             
        \define('CURRENT_DATE_FORMAT',$config['settings']['dateFormat'] ?? null);           
        \define('CURRENT_TIME_FORMAT',$config['settings']['timeFormat'] ?? null);  

        // Create app
        $container = AppContainer::create($console,$config);
        $container['responseFactory'] = function() {
            return new \Nyholm\Psr7\Factory\Psr17Factory();
        };
        AppFactory::setStreamFactory($container->get('responseFactory')); 
        Self::$app = AppFactory::create(
            $container->get('responseFactory'),
            $container
        );

        Self::$app->setBasePath(BASE_PATH);       
    }

    /**
     * Create Arikaim system. Create container services, load system routes 
     *     
     * @param boolean $showErrors   
     * @param array|null $config
     * @return void
    */
    public static function init(bool $showErrors = false, ?array $config = null): void 
    {        
        Self::systemInit($showErrors,false,$config);
        Session::start();
                    
        // Set router       
        Self::$app->getRouteCollector()->setDefaultInvocationStrategy(new ValidatorStrategy());
                          
        // map install page
        Self::$app->map(['GET'],'/admin/install','Arikaim\Core\App\InstallPage:loadInstall');
      
        // Add middlewares
        Self::initMiddleware();             
    }
    
    /**
     * Init middleware
     *
     * @param array|null $config 
     * @return void
     */
    public static function initMiddleware(?array $config = null): void
    {
        // add routing
        $routingMiddleware = new RoutingMiddleware(
            Self::$app->getRouteResolver(),          
            Self::$app->getRouteCollector(),
            function() {
                return Self::routes();
            }
        );
        Self::$app->add($routingMiddleware);     
        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
            Self::$app->add(new BodyParsingMiddleware());
        }              
     
        $errorMiddleware = new ErrorMiddleware(
            function() {
                return Self::page();
            },
            Self::$app->getResponseFactory()
        );
        Self::$app->add($errorMiddleware); 
        
        // add global middlewares
        $middlewares = $config['middleware'] ?? Self::config()->get('middleware',[]);      
        foreach ($middlewares as $item) {
            Self::$app->add(new $item());
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
     * @param bool $showErrors
     * @param array|null $config
     * @return void
    */
    public static function run(bool $showErrors = false, ?array $config = null): void 
    {      
        Self::init($showErrors,$config);    
        Self::$app->run();            
    }
    
    /**
     * Return error message
     *
     * @param string $errorCode Error code
     * @param array $params Erorr params
     * @param string|null $default
     * @return string
    */
    public static function getError(string $errorCode, array $params = [], ?string $default = 'UNKNOWN_ERROR'): string 
    {
        return Self::errors()->getError($errorCode,$params,$default);
    }

    /**
     * Return console root path.
     *
     * @return string
    */
    public static function getConsoleRootPath(): string
    {
        return \constant('ROOT_PATH') ?? $_SERVER['PWD'];
    }

    /**
     * Return root path.
     *
     * @param bool $console
     * @return string
    */
    public static function getRootPath(bool $console): string 
    {      
        // get root path for console run
        return ($console == false) ? \rtrim(\realpath($_SERVER['DOCUMENT_ROOT']),DIRECTORY_SEPARATOR) : Self::getConsoleRootPath();
    }

    /**
     * Return base path.
     *
     * @return string
    */
    public static function getBasePath(): string 
    {        
        return \constant('BASE_PATH') ?? '';      
    }

    /**
     * Return domain url.
     *
     * @return string
    */
    public static function getDomain(): string 
    {      
        return \constant('DOMAIN') ?? Self::resolveHost($_SERVER);
    }

    /**
     * Get host
     *
     * @return string
     */
    public static function getHost(): string 
    {      
        return \parse_url(DOMAIN,PHP_URL_HOST);
    }

    /**
     * Return true if script is run from console.
     *
     * @return boolean
    */
    public static function isConsole(): bool
    {
        return (\php_sapi_name() == 'cli');      
    }   
    
    /**
     * Resolve site host
     *
     * @param array $env
     * @return string
     */
    public static function resolveHost(array $env): string
    {
        // scheme
        $scheme = ((isset($env['HTTPS']) == true && $env['HTTPS'] !== 'off') || 
                (isset($env['REQUEST_SCHEME']) && $env['REQUEST_SCHEME'] === 'https') || 
                (isset($env['HTTP_X_FORWARDED_PROTO']) && $env['HTTP_X_FORWARDED_PROTO'] === 'https') == true) ? 'https' : 'http';
        // host
        if (empty($env['HTTP_HOST']) == false) {
            $host = $env['HTTP_HOST'];
        } else {             
            $host = $env['SERVER_NAME'] ?? '';    
            if (\preg_match('/^(\[[a-fA-F0-9:.]+\])(:\d+)?\z/',$host,$matches) == false) {           
                $host = (\strpos($host,':') !== false) ? \strstr($host,':', true) : $host;                             
            }           
        }

        return  $scheme . '://' . $host;
    }

    /**
     * Resolve site host
     *
     * @param array $env
     * @return string
     */
    public static function resolveBasePath(array $env, string $host): string
    {
        // path
        $scriptName = (string)\parse_url($env['SCRIPT_NAME'],PHP_URL_PATH);
        $scriptDir = \dirname($scriptName);      
        $uri = $env['REQUEST_URI'] ?? '';  
        $uri = (string)\parse_url($host . $uri,PHP_URL_PATH);
         
        // base path
        if (\stripos($uri,$scriptName) === 0) {
            $basePath = $scriptName;
        } elseif ($scriptDir !== '/' && \stripos($uri,$scriptDir) === 0) {
            $basePath = $scriptDir;
        } 
        $basePath = \rtrim(\str_ireplace('index.php','',$basePath ?? ''),DIRECTORY_SEPARATOR);
        
        return ($basePath == '/') ? '' : $basePath;        
    }   
}
