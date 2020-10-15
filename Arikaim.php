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
use Http\Factory\Guzzle\StreamFactory;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Routing\RouteContext;
use Slim\Middleware\ContentLengthMiddleware;
use Slim\Middleware\OutputBufferingMiddleware;
use Slim\Middleware\BodyParsingMiddleware;

use Arikaim\Container\Container;
use Arikaim\Core\Validator\ValidatorStrategy;
use Arikaim\Core\App\ServiceContainer;

use Arikaim\Core\System\Error\ApplicationError;
use Arikaim\Core\Http\Session;
use Arikaim\Core\Utils\Number;
use Arikaim\Core\Utils\DateTime;
use Arikaim\Core\System\Error\Renderer\HtmlPageErrorRenderer;
use Arikaim\Core\App\Install;

use Arikaim\Core\Http\Response;
use Arikaim\Core\Middleware\CoreMiddleware;
use Arikaim\Core\Db\Schema;
use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Middleware\RoutingMiddleware;
use Exception;

/**
 * Arikaim core class
 */
class Arikaim  
{
    const ARIKAIM_VERSION = '1.5.4';

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
     * Get item from container
     *
     * @param string $name
     * @return mixed|null
     */
    public static function get($name)
    {
        if (Self::$app->getContainer() == null) {
            return null;
        }

        return (Self::$app->getContainer()->has($name) == true) ? Self::$app->getContainer()->get($name) : null;
    }

    /**
     * Check item exists in container
     *
     * @param string $name Item name.
     * @return boolean
    */
    public static function has($name)
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
    * System init
    *
    * @param integer $showErrors
    * @param bool $console
    * @return void
    */
    public static function systemInit($showErrors = 0, $console = false)
    {
        \ini_set('display_errors',$showErrors);
        \ini_set('display_startup_errors',$showErrors);
        if ($showErrors == 0) {
            \error_reporting(0); 
        } else {
            \error_reporting(E_ALL); 
        }
       
        
        \set_error_handler(Self::end());
    
        Self::resolveEnvironment($_SERVER);

        // Init constants           
        \define('ROOT_PATH',Self::getRootPath());
        \define('BASE_PATH',Self::getBasePath());
        \define('DOMAIN',Self::getDomain());
        \define('APP_PATH',ROOT_PATH . BASE_PATH . DIRECTORY_SEPARATOR . 'arikaim');  
       
        $loader = new \Arikaim\Core\System\ClassLoader(BASE_PATH,ROOT_PATH,'Arikaim\Core',[
            'Arikaim\Extensions',
            'Arikaim\Modules'
        ]);
        $loader->register();
        
        \define('APP_URL',DOMAIN . BASE_PATH . '/arikaim');
        \define('CORE_NAMESPACE','Arikaim\\Core');
        \define('ARIKAIM_PACKAGE_NAME','arikaim/core');
        \define('CACHE_SAVE_TIME',4);
        \register_shutdown_function('\Arikaim\Core\Arikaim::end');
       
        // Create service container            
        AppFactory::setContainer(ServiceContainer::init(new Container(),$console)); 
        // Create app 
        Self::$app = AppFactory::create();
        Self::$app->setBasePath(BASE_PATH);       
    }

    /**
     * Create Arikaim system. Create container services, load system routes 
     * 
     * @param boolean $consoleMode - load routes 
     * @param integer $showErrors
     * @return void
    */
    public static function init($showErrors = 0) 
    {        
        Self::systemInit($showErrors);
        Session::start();
                    
        // Set router       
        Self::$app->getRouteCollector()->setDefaultInvocationStrategy(new ValidatorStrategy());
                
        // map control panel page
        Self::$app->map(['GET'],'/admin[/{language:[a-z]{2}}/]','Arikaim\Core\App\ControlPanel:loadControlPanel');
        // map install page
        Self::$app->map(['GET'],'/admin/install','Arikaim\Core\App\InstallPage:loadInstall');
      
        $hasDbError = Self::db()->hasError();
        // Add middlewares
        Self::initMiddleware($hasDbError);
        Self::addModulesMiddleware();    
        
        if ($hasDbError == true) {
            $renderer = new HtmlPageErrorRenderer(Self::errors());
            $applicationError = new ApplicationError(Self::response(),$renderer);  
            Self::checkInstall($applicationError);
            return;
        }

        // Set primary template           
        Self::view()->setPrimaryTemplate(Self::options()->get('primary.template'));   
        // DatTime and numbers format
        Number::setFormats(Self::options()->get('number.format.items',[]),Self::options()->get('number.format',null));
        // Set time zone
        DateTime::setTimeZone(Self::options()->get('time.zone'));
        // Set date and time formats
        DateTime::setDateFormats(Self::options()->get('date.format.items',[]),Self::options()->get('date.format',null));   
        DateTime::setTimeFormats(Self::options()->get('date.format.items',[]),Self::options()->get('time.format',null));                 
    }
    
    /**
     * Init middleware
     *
     * @return void
     */
    public static function initMiddleware($safeMode = false)
    {
        $routes = ($safeMode == false) ? Self::routes() : null;
        // add routing
        $routingMiddleware = new RoutingMiddleware(
            Self::$app->getRouteResolver(),          
            Self::$app->getRouteCollector(),
            $routes
        );
        Self::$app->add($routingMiddleware);
    
        // sanitize request body and client ip
        Self::$app->add(new CoreMiddleware(Self::getContainer()->get('config')['settings']));         
        Self::$app->add(new ContentLengthMiddleware());        
        Self::$app->add(new BodyParsingMiddleware());
        Self::$app->add(new OutputBufferingMiddleware(new StreamFactory(),OutputBufferingMiddleware::APPEND));
        
        // add modules middlewares 
        Self::addModules();  
    }

    /**
     * Add modules middlewares
     *   
     * @return boolean
     */
    public static function addModulesMiddleware()
    {
        $modules = Self::cache()->fetch('middleware.list');
        if (\is_array($modules) == false) {  
            if (Self::db()->hasError() == true) {
                return false;
            } 
            try {                
                $modules = Arikaim::packages()->create('module')->getPackgesRegistry()->getPackagesList([
                    'type'   => 2, // MIDDLEWARE 
                    'status' => 1    
                ]);         
                Self::cache()->save('middleware.list',$modules,CACHE_SAVE_TIME);   
            } catch(Exception $e) {
                return false;
            }
        }    

        foreach ($modules as $module) {             
            $instance = Factory::createModule($module['name'],$module['class']);
            if (\is_object($instance) == true) {
                Self::$app->add($instance);  
            }         
        }
        
        return true;
    }

    /**
     * Get version
     *
     * @return string
     */
    public static function getVersion() 
    {
        return Self::ARIKAIM_VERSION;    
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
     * @param integer $showErrors
     * @return void
    */
    public static function run($showErrors = 0) 
    {      
        Self::init($showErrors);    
        Self::$app->run();            
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
     * End handler
     *
     * @return void
     */
    public static function end() 
    {    
        $error = \error_get_last();    
        if (\error_reporting() == false || empty($error) == true) {
            return;
        }
    
        Self::get('cache')->clear();
        $renderer = new HtmlPageErrorRenderer(Self::errors());
        $applicationError = new ApplicationError(Self::response(),$renderer);  
        Self::checkInstall($applicationError);

        $output = $applicationError->renderError(Self::createRequest(),$error);            
        echo $output;
        exit();                                                                     
    }

    /**
     * Check Arikaim install
     *
     * @param object $applicationError
     * @return void
     */
    protected static function checkInstall($applicationError)
    {
        if (Install::isInstalled() == false) {                
            if (Install::isInstallPage() == true) {    
                $disabled = Self::get('config')->getByPath('settings/disableInstallPage',false);     
                if ($disabled != true) {                            
                    $output = Self::get('page')->getHtmlCode('system:install');  
                    echo $output;
                    exit();    
                }            
                // disbled install page
                $error = new Exception(Self::get('errors')->getError('INSTALL_PAGE_ERROR')); 
                $output = $applicationError->renderError(Self::createRequest(),$error);            
                echo $output;
                exit();                                             
            }                   
            if (Install::isApiInstallRequest() == true) {
                return Self::$app->run();
            } 
            // redirect to install page
            header('Location: ' . Install::getInstallPageUrl());
            return;                                      
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
        return (\defined('ROOT_PATH') == true) ? ROOT_PATH : $_SERVER['PWD'];
    }

    /**
     * Get console base path
     *
     * @return string
     */
    public static function getConsoleBasePath()
    {
        return (\defined('BASE_PATH') == true) ? BASE_PATH : '';       
    }

    /**
     * Return root path.
     *
     * @return string
    */
    public static function getRootPath() 
    {
        if (Self::isConsole() == false) {
            return \rtrim(\realpath($_SERVER['DOCUMENT_ROOT']),DIRECTORY_SEPARATOR);
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
            $path = \rtrim(\str_ireplace('index.php','',Self::$basePath), DIRECTORY_SEPARATOR);
            return ($path == '/') ? '' : $path;               
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
        return Self::$scheme . '://' . Self::$host;
    }

    /**
     * Get host
     *
     * @return string
     */
    public static function getHost() 
    {      
        return Self::$host;
    }

    /**
     * Return true if script is run from console.
     *
     * @return boolean
    */
    public static function isConsole()
    {
        return (\php_sapi_name() == 'cli');      
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
        $isHttps = 
            (isset($env['HTTPS']) == true && $env['HTTPS'] !== 'off') || 
            (isset($env['REQUEST_SCHEME']) && $env['REQUEST_SCHEME'] === 'https') || 
            (isset($env['HTTP_X_FORWARDED_PROTO']) && $env['HTTP_X_FORWARDED_PROTO'] === 'https');
       
        Self::$scheme = ($isHttps == true) ? 'https' : 'http';
              
        // host
        $serverName = (isset($env['SERVER_NAME']) == true) ? $env['SERVER_NAME'] : '';            
        $host = (isset($env['HTTP_HOST']) == true) ? $env['HTTP_HOST'] : $serverName;   

        if (\preg_match('/^(\[[a-fA-F0-9:.]+\])(:\d+)?\z/',$host,$matches) == false) {           
            $host = (\strpos($host,':') !== false) ? \strstr($host,':', true) : $host;                             
        } 
        Self::$host = $host;

        // path
        $scriptName = (string)\parse_url($env['SCRIPT_NAME'],PHP_URL_PATH);
        $scriptDir = \dirname($scriptName);      
        $uri = (isset($env['REQUEST_URI']) == true) ? $env['REQUEST_URI'] : '';  
        $uri = (string)\parse_url(Self::getDomain() . $uri,PHP_URL_PATH);
        
        if (\stripos($uri,$scriptName) === 0) {
            Self::$basePath = $scriptName;
        } elseif ($scriptDir !== '/' && \stripos($uri, $scriptDir) === 0) {
            Self::$basePath = $scriptDir;
        }       
    }
}
