<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System;

use Slim\DefaultServicesProvider;
use Illuminate\Database\Capsule\Manager;

use Arikaim\Container\Container;
use Arikaim\Core\Arikaim;
use Arikaim\Core\View\Template;
use Arikaim\Core\Events\EventsManager;
use Arikaim\Core\System\Session;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Cache\Cache;
use Arikaim\Core\Utils\Collection;
use Arikaim\Core\System\Path;
use Arikaim\Core\Packages\Module\ModulesManager;
use Arikaim\Core\Packages\Module\ModulePackage;
use \Arikaim\Core\System\Config;

/**
 * Create system services
 */
class ServiceContainer
{
    private $container;

    public function __construct(array $services = null)
    {
        $this->container = new Container($services);

        $this->registerSystemServices();
        $this->init(); 
        $this->registerCoreModules();     
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function registerSystemServices()
    {        
        $config = Config::loadConfig('config.php');
        $settings = isset($config['settings']) ? $config['settings'] : [];

        $default_settings = [
            'httpVersion' => '1.1',
            'responseChunkSize' => 4096,
            'addContentLengthHeader' => true
        ];
        $settings = array_merge($default_settings,$settings);
        $this->container['settings'] = function () use ($settings) {
            return new Collection($settings);
        };
        $defaultProvider = new DefaultServicesProvider();
        $defaultProvider->register($this->container);
    }

    public function boot()
    {
        $this->container->get('view');
        if (Arikaim::isConsole() == false) {
            $this->container->get('session');
        }
    }

    public function registerCoreModules()
    {
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
                    $instance = Factory::createModule($module['name'],$module['class']);
                    return $instance;
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
    }
 
    private function init()
    {
        // init class loader    
        $this->container['classLoader'] = function() {
            return new \Arikaim\Core\System\ClassLoader(ARIKAIM_BASE_PATH,ARIKAIM_ROOT_PATH);
        };
        // Config
        $this->container['config'] = function() {                         
            return new \Arikaim\Core\System\Config("config.php");          
        };

        // Cache 
        $this->container['cache'] = function($container) {   
            $disabeld = $container->get('config')->getByPath('settings/cache_disabled',false);       
            return new \Arikaim\Core\Cache\Cache(null,$disabeld);
        };
        // route strategy (Validator strategy)
        $this->container['foundHandler'] = function() {
            return new \Arikaim\Core\Validator\ValidatorStrategy();
        };
        // Errors  
        $this->container['errors'] = function() {
            $errors = new \Arikaim\Core\System\Errors();
            return $errors;
        };
        // Session 
        $this->container['session'] = function() {
            $session = new Session();
            return $session;
        };
        // Access
        $this->container['access'] = function() {
            return new \Arikaim\Core\Access\Access();
        };
        // Cookie 
        $this->container['cookies'] = function($container) {
            $request = $container->get('request');
            return new \Slim\Http\Cookies($request->getCookieParams());
        };
        // Init template view. 
        $this->container['view'] = function ($container) {   
            $paths = [Path::EXTENSIONS_PATH,Path::TEMPLATES_PATH];               
            $cache = (isset($container->get('config')['settings']['cache']) == true) ? Path::VIEW_CACHE_PATH : false;
            $debug = (isset($container->get('config')['settings']['debug']) == true) ? $container->get('config')['settings']['debug'] : false;
             
            $view = new \Arikaim\Core\View\View($paths,['cache' => $cache,'debug' => $debug]);
            // add template extensions
            $view->addExtension(new \Arikaim\Core\View\TemplateExtension());
            return $view;
        };    
        // Init page components.
        $this->container['page'] = function() {           
            $page = new \Arikaim\Core\View\Html\Page();
            return $page;
        };        
        // Init Eloquent ORM
        $this->container['db'] = function() {  
            try {  
              $db = new \Arikaim\Core\Db\Db($this->container->get('config')['db']);
            } catch(\PDOException $e) {
                $this->container->get('errors')->addError('DB_CONNECTION_ERROR');
            }      
            return $db;
        };   
        // boot db
        $this->container->get('db');       

        // Options
        $this->container['options'] = function() { 
            $options = Model::Options(); 
            $options->loadOptions();          
            return $options;
        };
        // Events manager 
        $this->container['event'] = function() {
            return new EventsManager();
        };
        // Mailer
        $this->container['mailer'] = function() {
            return new \Arikaim\Core\Mail\Mailer();
        };
        // Page not found handler
        $this->container['notFoundHandler'] = function() {
            return function ($request, $response) {
                $page = new \Arikaim\Core\Controlers\Pages\PageLoader;
                return $page->pageNotFound($request,$response);              
            };
        };
        // Logger
        $this->container['logger'] = function() {
            return new \Arikaim\Core\Logger\SystemLogger();
        };       
        // Jobs queue
        $this->container['jobs'] = function() {
            //$queue = new \Arikaim\Core\Jobs\JobsQueueManager();
            //return $queue;
        };   
        // http client  
        $this->container['http'] = function() {
            new \GuzzleHttp\Client();
        }; 
        // Application error handler
        $this->container['errorHandler'] = function() {
            return new \Arikaim\Core\System\ApplicationError();            
        };
        // Application Throwable error handler
        $this->container['phpErrorHandler'] = function() {
            return new \Arikaim\Core\System\ApplicationPHPError();            
        };
    }
}
