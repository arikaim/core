<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System;

use Slim\DefaultServicesProvider;
use Illuminate\Database\Capsule\Manager;

use Arikaim\Container\Container;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Events\EventsManager;
use Arikaim\Core\System\Session;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Collection\Collection;
use Arikaim\Core\System\Path;
use Arikaim\Core\Packages\Module\ModulePackage;
use \Arikaim\Core\System\Config;

/**
 * Create system services
 */
class ServiceContainer
{
    /**
     * Container
     *
     * @var Arikaim\Container\Container
     */
    private $container;

    /**
     * Constructor
     *
     * @param array|null $services
     */
    public function __construct(array $services = null)
    {
        $this->container = new Container($services);

        $this->registerSystemServices();       
        $this->init();       
        $this->registerCoreModules();      
    }

    /**
     * Return container
     *
     * @return Arikaim\Container\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Register default services
     *
     * @return void
     */
    public function registerSystemServices()
    {        
        $config = Config::read('config.php');
        $settings = isset($config['settings']) ? $config['settings'] : [];

        $default_settings = [
            'httpVersion'            => '1.1',
            'responseChunkSize'      => 4096,
            'addContentLengthHeader' => true,
            'routerCacheFile'        => Path::CACHE_PATH . "/routes.cache.php"
        ];
        $settings = array_merge($default_settings,$settings);
        $this->container['settings'] = function () use ($settings) {
            return new Collection($settings);
        };
        $defaultProvider = new DefaultServicesProvider();
        $defaultProvider->register($this->container);
    }

    /**
     * Boot some default services
     *
     * @return void
     */
    public function boot()
    {        
        $this->container->get('view');
        if (Arikaim::isConsole() == false) {
            $this->container->get('session');
        }
    }

    /**
     * Add module services in container
     *
     * @return bool
     */
    public function registerCoreModules()
    {
        if ($this->container->get('db')->isValidConnection() == false) {
            return false;
        }
        
        if (Manager::schema()->hasTable('modules') == false) {
            return false;
        }
        $modules = $this->container->get('cache')->fetch('services.list');
        if (is_array($modules) == false) {
            $modules = Model::Modules()->getList(ModulePackage::getTypeId('service'),1);
            $this->container->get('cache')->save('services.list',$modules,2);    
        } 
            
        foreach ($modules as $module) {
            $service_name = $module['service_name'];
            if (empty($service_name) == false) {
                // add to container
                $this->container[$service_name] = function() use($module) {
                    return Factory::createModule($module['name'],$module['class']);
                };
            }
            if ($module['bootable'] == true) {
                $this->container->get($service_name)->boot();
            }
            // load facade class alias
            if (isset($module['facade_alias']) == true) {
                $this->container->get('classLoader')->loadClassAlias($module['facade_class'],$module['facade_alias']);
            }
        }
        return true;
    }
    
    /**
     * Init default services
     *
     * @return void
     */
    private function init()
    {
        // Init class loader    
        $this->container['classLoader'] = function() {
            return new \Arikaim\Core\System\ClassLoader(ARIKAIM_BASE_PATH,ARIKAIM_ROOT_PATH);
        };
        // Cache 
        $this->container['cache'] = function($container) {   
            return new \Arikaim\Core\Cache\Cache(null,true,$container->get('settings'));
        };
        // Config
        $this->container['config'] = function($container) {    
            $cache = $container->get('cache');                         
            $config = new \Arikaim\Core\System\Config("config.php",$cache);         
            return $config;
        }; 
        // Route strategy (Validator strategy)
        $this->container['foundHandler'] = function() {
            return new \Arikaim\Core\Validator\ValidatorStrategy();
        };
        // Errors  
        $this->container['errors'] = function() {
            return new \Arikaim\Core\System\Error\Errors();          
        };
        // Session 
        $this->container['session'] = function() {
            return new Session();
        };
        // Access
        $this->container['access'] = function() {
            return new \Arikaim\Core\Access\Access();
        };
        // Auth
        $this->container['auth'] = function() {
            return new \Arikaim\Core\Access\Authenticate();
        };
        // Cookie 
        $this->container['cookies'] = function($container) {
            $request = $container->get('request');
            return new \Slim\Http\Cookies($request->getCookieParams());
        };
        // Init template view. 
        $this->container['view'] = function ($container) {   
            $paths = [Path::EXTENSIONS_PATH,Path::TEMPLATES_PATH,Path::COMPONENTS_PATH];               
            $cache = (isset($container->get('config')['settings']['cache']) == true) ? Path::VIEW_CACHE_PATH : false;
            $debug = (isset($container->get('config')['settings']['debug']) == true) ? $container->get('config')['settings']['debug'] : true;
             
            return new \Arikaim\Core\View\View($paths,['cache' => $cache,'debug' => $debug,'autoescape' => false]);           
        };    
        // Init page components.
        $this->container['page'] = function() {           
            return new \Arikaim\Core\View\Html\Page();
        };        
        // Init Eloquent ORM
        $this->container['db'] = function() {  
            try {  
                $relations = $this->container->get('config')->load('relations.php');
                $db = new \Arikaim\Core\Db\Db($this->container->get('config')['db'],$relations);
            } catch(\PDOException $e) {
                $this->container->get('errors')->addError('DB_CONNECTION_ERROR');
            }      
            return $db;
        };   
        // boot db
        $this->container->get('db');       

        // Options
        $this->container['options'] = function() { 
            return Model::Options()->loadOptions();         
        };
        // Events manager 
        $this->container['event'] = function() {
            return new EventsManager();
        };
        // Mailer
        $this->container['mailer'] = function() {
            return new \Arikaim\Core\Mail\Mailer();
        };
        // Modules
        $this->container['module'] = function() {   
            return new \Arikaim\Core\Packages\Module\ModulesManager();  
        };
        // Drivers
        $this->container['driver'] = function() {   
            return new \Arikaim\Core\Driver\DriverManager();  
        };
        // Page not found handler       
      //  $this->container['notFoundHandler'] = function() {
            // TODO
           // return function ($request, $response) {
            //    $page = new \Arikaim\Core\Controllers\PageLoader;              
          //      return $page->pageNotFound($request,$response);              
          //  };
       // };
        // Logger
        $this->container['logger'] = function($container) {           
            $enabled = $container->get('options')->get('logger',true);
            return new \Arikaim\Core\Logger\Logger($enabled);
        };       
        // Jobs queue
        $this->container['queue'] = function() {
            return new \Arikaim\Core\Queue\QueueManager();          
        };   
        // http client  
        $this->container['http'] = function() {
            return new \GuzzleHttp\Client();
        }; 
        // Application error handler
        $this->container['errorHandler'] = function($container) {
            $show_details = $container->get('config')['settings']['debug'];
            $show_trace = $container->get('config')['settings']['debugTrace'];
            return new \Arikaim\Core\System\Error\ApplicationError($show_details,$show_trace);            
        };
        // Application Throwable error handler
        $this->container['phpErrorHandler'] = function($container) {
            $show_details = $container->get('config')['settings']['debug'];
            $show_trace = $container->get('config')['settings']['debugTrace'];
            return new \Arikaim\Core\System\Error\ApplicationPHPError($show_details,$show_trace);            
        };
        // Set cache status
        $cache = $this->container->get('config')->getByPath('settings/cache',false);     
        $this->container->get('cache')->setStatus($cache);              
    }
}
