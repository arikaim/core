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
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Routing\RouteContext;
use Exception;

use Arikaim\Container\Container;
use Arikaim\Core\Validator\ValidatorStrategy;
use Arikaim\Core\App\ServiceContainer;
use Arikaim\Core\App\SystemRoutes;
use Arikaim\Core\Middleware\MiddlewareManager;
use Arikaim\Core\System\Error\ApplicationError;
use Arikaim\Core\Http\Session;
use Arikaim\Core\Utils\Number;
use Arikaim\Core\Utils\DateTime;
use Arikaim\Core\Http\Url;
use Arikaim\Core\Utils\Path;
use Arikaim\Core\Utils\Factory;
use Arikaim\Core\System\Error\Renderer\HtmlPageErrorRenderer;
use Arikaim\Core\Extension\Modules;
use Arikaim\Core\System\Composer;
use Arikaim\Core\App\Install;
use Arikaim\Core\View\Template\Template;
use Arikaim\Core\Models\AccessTokens;

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
     * Create Arikaim system. Create container services, load system routes 
     * 
     * @param boolean $consoleMode - load routes 
     * @param integer $showErrors
     * @return void
    */
    public static function init($showErrors = 0) 
    {        
        \ini_set('display_errors',$showErrors);
        \ini_set('display_startup_errors',$showErrors);
        \error_reporting(E_ALL); 

        if ($showErrors == 0) {
            \set_error_handler(function () {
                return Self::end();
            });
        }
       
        Self::resolveEnvironment($_SERVER);

        // Init constants   
        if (defined('ROOT_PATH') == false) {
            \define('ROOT_PATH',Self::getRootPath());
        }
        \define('BASE_PATH',Self::getBasePath());
        \define('DOMAIN',Self::getDomain());
        \define('APP_PATH',ROOT_PATH . BASE_PATH . DIRECTORY_SEPARATOR . 'arikaim');  
       
        $loader = new \Arikaim\Core\System\ClassLoader(BASE_PATH,ROOT_PATH,'Arikaim\Core',[
            'Arikaim\Extensions',
            'Arikaim\Modules'
        ]);
        $loader->register();
        
        Url::setAppUrl('/arikaim');
        Path::setAppPath('arikaim');
        Factory::setCoreNamespace("Arikaim\\Core");

        // Load global functions
        $loader->LoadClassFile('\\Arikaim\\Core\\App\\Globals');
         
        \register_shutdown_function("\Arikaim\Core\Arikaim::end");
       
        // Create service container            
        AppFactory::setContainer(ServiceContainer::init(new Container()));
        
        // Create app 
        Self::$app = AppFactory::create();
        Self::$app->setBasePath(BASE_PATH);
            
        if (Arikaim::isConsole() == false) {   
            Session::start();
                       
            // Set router 
            $validatorStrategy = new ValidatorStrategy(Self::get('event'),Self::get('errors'));
            Self::$app->getRouteCollector()->setDefaultInvocationStrategy($validatorStrategy);
        
            Self::$app->getRouteCollector()->setCacheFile(Path::CACHE_PATH . "/routes.cache.php");     
            // Map routes                       
            SystemRoutes::mapSystemRoutes(); 
            // Boot db
            Self::get('db');  
           
            // Add default middleware
            MiddlewareManager::init(Self::getContainer()->get('config')['settings']); 
            
            Self::mapRoutes();   
            
            // Set primary template
            Template::setPrimary(Self::options()->get('primary.template'));
            // DatTime and numbers format
            Number::setFormats(Self::options()->get('number.format.items',[]),Self::options()->get('number.format',null));
           
            // Set time zone
            DateTime::setTimeZone(Arikaim::options()->get('time.zone'));

            // Set date and time formats
            DateTime::setDateFormats(Arikaim::options()->get('date.format.items',[]),Arikaim::options()->get('date.format',null));   
            DateTime::setTimeFormats(Arikaim::options()->get('date.format.items',[]),Arikaim::options()->get('time.format',null));                  
        }      

    }
    
    /**
     * Get version
     *
     * @return string
     */
    public static function getVersion() 
    {
        return Composer::getInstalledPackageVersion(ROOT_PATH . BASE_PATH,Self::getCorePackageName());        
    }

    /**
     * Map routes
     *     
     * @return boolean
     */
    public static function mapRoutes()
    {
        $routes = Self::routes()->getAllRoutes();
        $accessToken = new AccessTokens();

        foreach($routes as $item) {
            $methods = \explode(',',$item['method']);
            $handler = $item['handler_class'] . ":" . $item['handler_method'];   

            $route = Self::$app->map($methods,$item['pattern'],$handler);
            // auth middleware
            if ($item['auth'] > 0) {
                $options['redirect'] = (empty($item['redirect_url']) == false) ? Url::BASE_URL . $item['redirect_url'] : null;      
                
                $userProvider = ($item['auth'] == 4) ? $accessToken : null;                
                $middleware = Self::access()->middleware($item['auth'],$options,$userProvider);    
    
                if ($middleware != null && \is_object($route) == true) {
                    $route->add($middleware);
                }
            }                                                   
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
        if (\error_reporting() == true) {
            $error = \error_get_last();    
            if (empty($error) == false) {               
                Self::get('cache')->clear();
                $renderer = new HtmlPageErrorRenderer(Self::errors());
                $applicationError = new ApplicationError(Self::response(),$renderer);  
                if (Install::isInstalled() == false) {       
                    if (Install::isInstallPage() == true) {                          
                        $output = Self::get('page')->getHtmlCode('system:install');  
                        echo $output;
                        exit();             
                    }                   
                    if (Install::isApiInstallRequest() == true) {
                        return Self::$app->run();
                    } 
                    $error = new Exception(Self::get('errors')->getError('NOT_INSTALLED_ERROR'));                      
                } 
        
                $output = $applicationError->renderError(Self::createRequest(),$error);            
                echo $output;
                exit();                                                  
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
        return (\defined('ROOT_PATH') == true) ? ROOT_PATH : $_SERVER['PWD'];
    }

    /**
     * Get console base path
     *
     * @return string
     */
    public static function getConsoleBasePath()
    {
        return (\defined('BASE_PATH') == true) ? BASE_PATH : "";       
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
        return (\php_sapi_name() == "cli") ? true : false;         
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
