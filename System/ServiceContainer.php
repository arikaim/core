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

use Arikaim\Container\Container;
use Slim\Collection;
use Slim\DefaultServicesProvider;
use Arikaim\Core\Arikaim;
use Arikaim\Core\View\Template;
use Arikaim\Core\Events\EventsManager;
use Arikaim\Core\System\Session;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Utils\Factory;

class ServiceContainer
{
    private $container;

    public function __construct(array $services = null)
    {
        $this->container = new Container($services);
     
        $settings = isset($services['settings']) ? $services['settings'] : [];
        $this->registerSystemServices($settings);
        $this->init(); 
        $this->registerCoreModules();     
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function registerSystemServices($settings = [])
    {        
        $default_settings = [
            'httpVersion' => '1.1',
            'responseChunkSize' => 4096,
            'outputBuffering' => 'append',
            'determineRouteBeforeAppMiddleware' => false,
            'displayErrorDetails' => false,
            'addContentLengthHeader' => true,
            'routerCacheFile' => false,
        ];

        $this->container['settings'] = function () use ($default_settings,$settings) {
            return new Collection(array_merge($default_settings,$settings));
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
        $modules = $this->container->get('options')->get('core.modules');
        $modules = json_decode($modules,true);
        if (is_array($modules) == false) {
            return false;
        }
        foreach ($modules as $module) {  
            $service_name = $module['service_name'];
            $this->container[$service_name] = function() use($module) {
                $instance = Factory::createModule($module['path'],$module['class']);
                return $instance;
            };
            if ($module['bootable'] == true) {
                $this->container->get($service_name);
            }
        }
    }
 
    private function init()
    {
        // init class loader    
        $this->container['classLoader'] = function() {
            $loader = new \Arikaim\Core\System\ClassLoader(Arikaim::getBasePath(),Arikaim::getRootPath());
            return $loader;
        };
        // Config
        $this->container['config'] = function() {
            $config = new \Arikaim\Core\System\Config("config.php");
            return $config;
        };
        // Errors  
        $this->container['errors'] = function() {
            $errors = new \Arikaim\Core\Errors\Errors();
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
        $this->container['cookies'] = function() {
            $request = $this->container->get('request');
            return new \Slim\Http\Cookies($request->getCookieParams());
        };
        // Init template view. 
        $this->container['view'] = function () {       
            $view = new \Arikaim\Core\View\View(Template::getTemplatesPath(),['cache' => false]);
            // add template extensions
            $view->addExtension(new \Arikaim\Core\View\TemplateExtension());
            return $view;
        };    
        // Init page components.
        $this->container['page'] = function() {           
            $page = new \Arikaim\Core\View\Html\Page();
            return $page;
        }; 
        // DB
        $this->initDb();        
        // Options
        $this->container['options'] = function() { 
            $options = Model::Options(); 
            $options->loadOptions();          
            return $options;
        };
        // Events manager 
        $this->container['event'] = function() {
            $events = new EventsManager();
            return $events;
        };
        // Mailer
        $this->container['mailer'] = function() {
            $mailer = new \Arikaim\Core\System\Mailer();
            return $mailer;
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
            $logger = new \Arikaim\Core\Logger\SystemLogger();
            return $logger;
        };       
        // Jobs queue
        $this->container['jobs'] = function() {
            $queue = new \Arikaim\Core\Jobs\JobsQueueManager();
            return $queue;
        };
    }

    private function initDb()
    {
        // Init Eloquent ORM
        $this->container['db'] = function() {  
            try {  
                /*                
                $capsule = new \Illuminate\Database\Capsule\Manager;
                $capsule->addConnection($this->container->get('config')['db']);
                $capsule->setAsGlobal();
                // schema db
                $schema_db = $this->container->get('config')['db'];               
                $schema_db['database'] = 'information_schema';               
                $capsule->addConnection($schema_db,"schema");
                $capsule->bootEloquent();
              //  $result = \Arikaim\Core\System\Install::checkDbConnection($capsule->connection());
               
               // if ($result == false) {
              //      $this->container->get('errors')->addError('DB_CONNECTION_ERROR');
              //      $this->container->get('errors')->addError('SYSTEM_ERROR');
              //  }   
              */
              $db = new \Arikaim\Core\Db\Db($this->container->get('config')['db']);
            } catch(\PDOException $e) {
                $this->container->get('errors')->addError('DB_CONNECTION_ERROR');
            }      
            return $db;
        };   
        // boot db
        $this->container->get('db');
    }
}
