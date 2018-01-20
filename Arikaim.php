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

use \Slim\Container;
use Arikaim\Core\System\Session;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Db\Model;
use Arikaim\Core\ClassLoader;
use Arikaim\Core\System\System;
use Arikaim\Core\System\Config;
use Arikaim\Core\View\Html\Template;
use Arikaim\Core\Events\EventsManager;
use Arikaim\Core\Utils\Factory;

/**
 * Arikaim core class
 */
class Arikaim  
{
    public static $app;
    private static $container;
    private static $uri;

    public static function init() 
    {        
        // shutdown function
        register_shutdown_function("\Arikaim\Core\Arikaim::end");
        // container
        Self::initContainer(); 
        // add system routes  
        Self::mapRoutes();
        // site stats middleware
        if (Self::options('logger.stats') == true) {                        
            Self::$app->add(new \Arikaim\Core\Middleware\SiteStats);   
        }    
        // Middleware for sanitize request body and client ip
        Self::$app->add(new \Arikaim\Core\Middleware\CoreMiddleware());  
        // Middleware for dynamic route loading
     //   Self::$app->add(new \Arikaim\Core\Middleware\RouteLoader());
    }   

    private static function mapExtensionsRoutes()
    {
        if (Self::errors()->hasError("DB_CONNECTION_ERROR") == true) {
            return false;
        }
        $routes = Model::Routes()->getRoutes();
        if (is_array($routes) == true) {
            foreach($routes as $item) {
                $path = $item['path'] . $item['pattern'];
                $methods = explode(',',$item['method']);
                $handler = Factory::getExtensionControlerCallable($item['extension_name'],$item['handler_class'],$item['handler_method']);
                $middleware = Factory::createAuthMiddleware($item['auth']); 
                $route = Self::$app->map($methods,$path,$handler);
                if ($middleware != null) {
                    $route->add($middleware);
                }
            }
        }
        return true;
    }

    private static function mapRoutes()
    {
        $session_auth = new \Arikaim\Core\Middleware\SessionAuthentication();
        $jwt_auth =  new \Arikaim\Core\Middleware\JwtAuthentication();

        $controles_namespace = \Arikaim\Core\Controlers\Controler::getControlersNamespace();

        Self::$app->get('/',"$controles_namespace\Pages\PageLoader:loadPage");

        // Session
        Self::$app->put('/api/session/',"$controles_namespace\Api\SessionApi:setValue")->add($jwt_auth);
        Self::$app->get('/api/session/',"$controles_namespace\Api\SessionApi:getInfo")->add($jwt_auth);
        Self::$app->get('/api/session/restart/',"$controles_namespace\Api\SessionApi:restart")->add($jwt_auth);

        // UI Component       
        Self::$app->get('/api/ui/component/{name}[/{params:.*}]',"$controles_namespace\Api\Ui\ComponentApi:loadComponent")->add($session_auth);

        // UI Page  
        Self::$app->get('/api/ui/page/{name}',"$controles_namespace\Api\Ui\PageApi:loadPage")->add($session_auth);
        Self::$app->get('/api/ui/page/properties/',"$controles_namespace\Api\Ui\PageApi:loadPageProperties")->add($session_auth);  

        // Upload File
        Self::$app->post('/api/ui/upload/file/{name}',"$controles_namespace\Api\FileApi:upload")->add($jwt_auth);

        // Control Panel
        Self::$app->get('/admin[/]',"$controles_namespace\Pages\PageLoader:loadControlPanel");
        // Install
        Self::$app->post('/admin/api/install/',"$controles_namespace\Api\AdminApi:install")->add($session_auth);    
        // Update
        Self::$app->get('/admin/api/update/',"$controles_namespace\Api\AdminApi:update")->add($jwt_auth);    
        Self::$app->get('/admin/api/update/check',"$controles_namespace\Api\AdminApi:updateCheckVersion")->add($jwt_auth);    
        // Admin user
        Self::$app->post('/admin/api/user/login/',"$controles_namespace\Api\UsersApi:adminLogin")->add($session_auth); 
        Self::$app->post('/admin/api/user/reset-passord',"$controles_namespace\Api\UsersApi:resetPassword")->add($jwt_auth); 
        Self::$app->post('/admin/api/user/',"$controles_namespace\Api\UsersApi:changeDetails")->add($jwt_auth);
        Self::$app->get('/admin/api/user/logout/',"$controles_namespace\Api\UsersApi:logout");
        // Languages
        Self::$app->post('/admin/api/language/',"$controles_namespace\Api\LanguageApi:add")->add($jwt_auth); 
        Self::$app->delete('/admin/api/language/{uuid}',"$controles_namespace\Api\LanguageApi:remove")->add($jwt_auth); 
        Self::$app->put('/admin/api/language/change/{language_code}',"$controles_namespace\Api\LanguageApi:changeLanguage"); 
        Self::$app->put('/admin/api/language/status/{uuid}/{status}',"$controles_namespace\Api\LanguageApi:setStatus")->add($jwt_auth); 
        Self::$app->put('/admin/api/language/default/{uuid}',"$controles_namespace\Api\LanguageApi:setDefault")->add($jwt_auth); 
        Self::$app->put('/admin/api/language/move/{uuid}/{after_uuid}',"$controles_namespace\Api\LanguageApi:changeOrder")->add($jwt_auth); 
        // Extensions
        Self::$app->put('/admin/api/extension/install/{name}',"$controles_namespace\Api\ExtensionsApi:install")->add($jwt_auth); 
        Self::$app->put('/admin/api/extension/status/{name}/{status}',"$controles_namespace\Api\ExtensionsApi:changeStatus")->add($jwt_auth); 
        Self::$app->put('/admin/api/extension/uninstall/{name}',"$controles_namespace\Api\ExtensionsApi:unInstall")->add($jwt_auth); 
        // Templates
        Self::$app->put('/admin/api/template/current/{name}',"$controles_namespace\Api\TemplatesApi:setCurrent")->add($jwt_auth); 
        // Options
        Self::$app->get('/admin/api/options/{key}',"$controles_namespace\Api\OptionsApi:get")->add($jwt_auth);
        Self::$app->put('/admin/api/options/',"$controles_namespace\Api\OptionsApi:save")->add($jwt_auth);
        Self::$app->post('/admin/api/options/',"$controles_namespace\Api\OptionsApi:saveOptions")->add($jwt_auth);
        // Logs
        Self::$app->delete('/admin/api/logs/',"$controles_namespace\Api\AdminApi:clearLogs")->add($jwt_auth);
        
        // Map extensions routes
        Self::mapExtensionsRoutes();
    }

    public static function __callStatic($name, $arguments)
    {        
        if (isset($arguments[0]) == true) {
            $key = $arguments[0];
            $service = Self::$container->get($name);
            if (is_array($service) == true) {
                if (isset($service[$name]) == true) {
                    $result = Utils::arrayGetValue($service[$name],$key);
                } else {
                    $result = Utils::arrayGetValue($service,$key);
                } 
                return $result;               
            }            
            if (is_object($service) == true) {
                if ($service instanceof \Arikaim\Core\Interfaces\CollectionInterface) {
                    $result = Utils::arrayGetValue($service->toArray(),$key);
                    return $result;
                }
            }            
        } 
        try {
            return Self::$container->get($name);
        } catch(\Exception $e) {
            return null;
        }
    }

    public static function set($service_name,$value)
    {
        Self::$container[$service_name] = $value;
    }

    /**
     * Create 
     *
     * @return void
     */
    public static function create() 
    {
        // create container
        Self::$container = new \Slim\Container;
        // url
        Self::$uri = Self::request()->getUri();

        // init class loader    
        Self::$container['classLoader'] = function() {
            $loader = new \Arikaim\Core\System\ClassLoader(Self::getBasePath());
            return $loader;
        };
        Self::classLoader()->register();

        Self::$container['config'] = function() {
            $config = new \Arikaim\Core\System\Config("config.php");
            return $config;
        };
        Self::$container['settings'] = Self::config('settings');
        Self::$app = new \Slim\App(Self::$container);
       
        // set start time
        System::initStartTime();
        Self::init();
    }
  
    public static function run() 
    {
        Self::create();
        Self::logger()->info("System start");
        Self::$app->run();
    }

    private static function initDb()
    {
        // Init Eloquent ORM
        Self::$container['db'] = function() {  
            try {                  
                $capsule = new \Illuminate\Database\Capsule\Manager;
                $capsule->addConnection(Self::config('db'));
                $capsule->setAsGlobal();
                
                // schema db
                $schema_db = Self::config('db');
                $schema_db['database'] = 'information_schema';               
                $capsule->addConnection($schema_db,"schema");

                // $capsule->setEventDispatcher(new Dispatcher(new Container));
                $capsule->bootEloquent();
                $result = \Arikaim\Core\Install\Install::checkDbConnection($capsule->connection());
                if ($result == false) {
                    Self::errors()->addError('DB_CONNECTION_ERROR');
                }        
            } catch(\PDOException $e) {
                Self::errors()->addError('DB_CONNECTION_ERROR');
            }      
            return $capsule;
        };
        // Boot db 
        Self::db();           
    }

    private static function initView()
    {
        // Init twig template view. 
        Self::$container['view'] = function () {    
            $view = new \Arikaim\Core\View\View(Template::getTemplatePath(),['cache' => false]);
            $base_path = Self::getBasePath();
            $loader = $view->getLoader();
            $loader->addPath(Self::getViewPath());
            $loader->addPath(Template::getTemplatePath(Template::SYSTEM_TEMPLATE_NAME));
            // add template extensions
            $view->addExtension(new \Arikaim\Core\View\TemplateExtension());
            return $view;
        };
        // Page type
        Self::$container['pageType'] = 1; 
        // Init template components.
        Self::$container['templateComponents'] = function() {
            $components = new \Arikaim\Core\View\ComponentsProperties();
            return $components;
        };
        // Init page components.
        Self::$container['page'] = function() {           
            $service['page']['properties'] = new \Arikaim\Core\Utils\Collection();
            return $service;
        };
        // Init template components.       
        Self::$container['parent.component'] = "";
        // init template
        Self::$container['template'] = function() {
            $html_template = new \Arikaim\Core\View\Html\Template();
            return $html_template;
        };
    }

    private static function initContainer() 
    {
        // Errors  
        Self::$container['errors'] = function() {
            $errors = new \Arikaim\Core\Errors\Errors();
            return $errors;
        };
        // Session 
        Self::$container['session'] = function() {
            $session = new Session();
            return $session;
        };
        Self::session();

        // Cookie 
        Self::$container['cookies'] = function(){
            $request = Arikaim::request();
            return new \Slim\Http\Cookies($request->getCookieParams());
        };
        
        // Init View
        Self::initView();
        // Init DB
        Self::initDb();
        
        // Load options
        Self::$container['options'] = function() { 
            $options = Model::Options(); 
            $options->loadOptions();          
            return $options;
        };
       
        // Events manager 
        Self::$container['event'] = function() {
            $events = new EventsManager();
            return $events;
        };

        // Init mailer
        Self::$container['mailer'] = function() {
            $config = Arikaim::config('mailer');
            if (Arikaim::options('mailer.use.sendmail') == true) {
                $transport = new Swift_SendmailTransport('/usr/sbin/sendmail -bs');
            } else {
                $transport = new Swift_SmtpTransport(Arikaim::options('mailer.smpt.host'), Arikaim::options('mailer.smpt.port'));
                $transport->setUsername(Arikaim::options('mailer.username'));
                $transport->setPassword(Arikaim::options('mailer.password'));            
            }
            $mailer = new Swift_Mailer($transport);
            return $mailer;
        };

        // Page not found handler
        Self::$container['notFoundHandler'] = function() {
            return function ($request, $response) {
                $page = new \Arikaim\Controlers\Pages\PageLoader;
                return $page->pageNotFound($request,$response);              
            };
        };

        // Init logger
        Self::$container['logger'] = function() {
            $logger = new \Arikaim\Core\Logger\SystemLogger();
            return $logger;
        };
    }

    public static function getTemplateVars()
    {
        $base_path = Self::getBasePath(); 
        $base_url  = Self::getBaseUrl();
        return array(
            'base_path'             => $base_path,
            'base_url'              => $base_url,
            'template_url'          => Template::getTemplateURL(),
            'template_name'         => Template::getTemplateName(),
            'ui_path'               => $base_path . Self::getViewPath(),
            'system_template_url'   => Template::getTemplateURL(Template::SYSTEM_TEMPLATE_NAME),
            'system_template_name'  => Template::SYSTEM_TEMPLATE_NAME,
            'ui_library_path'       => $base_path . DIRECTORY_SEPARATOR . Self::getViewPath() . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR,
            'ui_library_url'        => Self::getViewURL() . "/library/"            
        );
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

    public static function getLanguage() 
    {  
        $language = Self::session()->get('language');
        if ($language == null) {
            $language = Self::cookies()->get('language');     
        }
        if (($language == "") || ($language == null)) { 
            try {
                $language = Model::Language()->getDefaultLanguage();
            } catch(\Exception $e) {
                $language = Self::config('settings/defaultLanguage');
                if (empty($language) == true) {
                    $language = "en";
                }   
            }           
        }            
        return $language;
    }

    public static function setLanguage($language_code) 
    {
        Self::session()->set('language',$language_code);
        Self::cookies()->set('language',$language_code);
        return $language_code;
    }

    public static function getRootPath() 
    {
        return rtrim(realpath($_SERVER['DOCUMENT_ROOT']),DIRECTORY_SEPARATOR);
    }

    public static function getBasePath() 
    {        
        $path = rtrim(str_ireplace('index.php','',Self::$uri->getBasePath()), DIRECTORY_SEPARATOR);
        if ($path == "/") $path = "";
        return $path;
    }
    
    public static function getViewPath() 
    {
        return 'arikaim' . DIRECTORY_SEPARATOR . 'view';       
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
}
