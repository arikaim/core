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
     * Add module services in container
     *
     * @return Container
     */
    public static function registerCoreModules($container)
    {
        if ($container->get('db')->isValidConnection() == false) {
            return $container;
        }
        
        if (Manager::schema()->hasTable('modules') == false) {
            return $container;
        }
        $modules = $container->get('cache')->fetch('services.list');
        if (is_array($modules) == false) {
            $modules = Model::Modules()->getList(ModulePackage::getTypeId('service'),1);
            $container->get('cache')->save('services.list',$modules,2);    
        } 
            
        foreach ($modules as $module) {
            $service_name = $module['service_name'];
            if (empty($service_name) == false) {
                // add to container
                $container[$service_name] = function() use($module) {
                    return Factory::createModule($module['name'],$module['class']);
                };
            }
            if ($module['bootable'] == true) {
                $container->get($service_name)->boot();
            }
            // load facade class alias
            if (isset($module['facade_alias']) == true) {
                $container->get('classLoader')->loadClassAlias($module['facade_class'],$module['facade_alias']);
            }
        }
        return $container;
    }
    
    /**
     * Init default services
     *
     * @return Container
     */
    public static function init($container)
    {
        // settings
        $config = Config::read('config.php');
        $settings = isset($config['settings']) ? $config['settings'] : [];       
        $container['settings'] = function () use ($settings) {
            return new Collection($settings);
        };
        // Init class loader    
        $container['classLoader'] = function() {
            return new \Arikaim\Core\System\ClassLoader(ARIKAIM_BASE_PATH,ARIKAIM_ROOT_PATH);
        };
        // Cache 
        $container['cache'] = function($container) {            
            $enabled = $container->get('settings')->get('cache',false);     
            return new \Arikaim\Core\Cache\Cache($enabled);
        };
        // Config
        $container['config'] = function($container) {    
            $cache = $container->get('cache');                         
            $config = new \Arikaim\Core\System\Config("config.php",$cache);         
            return $config;
        }; 
        // Errors  
        $container['errors'] = function() {
            return new \Arikaim\Core\System\Error\Errors();          
        };
        // Session 
        $container['session'] = function() {
            return new Session();
        };
        // Access
        $container['access'] = function() {
            return new \Arikaim\Core\Access\Access();
        };
        // Auth
        $container['auth'] = function() {
            return new \Arikaim\Core\Access\Authenticate();
        };
        // Init template view. 
        $container['view'] = function ($container) {   
            $paths = [Path::EXTENSIONS_PATH,Path::TEMPLATES_PATH,Path::COMPONENTS_PATH];               
            $cache = (isset($container->get('config')['settings']['cache']) == true) ? Path::VIEW_CACHE_PATH : false;
            $debug = (isset($container->get('config')['settings']['debug']) == true) ? $container->get('config')['settings']['debug'] : true;
             
            return new \Arikaim\Core\View\View($paths,['cache' => $cache,'debug' => $debug,'autoescape' => false]);           
        };    
        // Init page components.
        $container['page'] = function() {           
            return new \Arikaim\Core\View\Html\Page();
        };        
        // Init Eloquent ORM
        $container['db'] = function($container) {  
            try {  
                $relations = $container->get('config')->load('relations.php');
                $db = new \Arikaim\Core\Db\Db($container->get('config')['db'],$relations);
            } catch(\PDOException $e) {
                $container->get('errors')->addError('DB_CONNECTION_ERROR');
            }      
            return $db;
        };   
        // boot db
        $container->get('db');       

        // Options
        $container['options'] = function() { 
            return Model::Options()->loadOptions();         
        };
        // Events manager 
        $container['event'] = function() {
            return new EventsManager();
        };
        // Mailer
        $container['mailer'] = function() {
            return new \Arikaim\Core\Mail\Mailer();
        };
        // Modules
        $container['module'] = function() {   
            return new \Arikaim\Core\Packages\Module\ModulesManager();  
        };
        // Drivers
        $container['driver'] = function() {   
            return new \Arikaim\Core\Driver\DriverManager();  
        };
        // Logger
        $container['logger'] = function($container) {           
            $enabled = $container->get('options')->get('logger',true);
            return new \Arikaim\Core\Logger\Logger($enabled);
        };       
        // Jobs queue
        $container['queue'] = function() {
            return new \Arikaim\Core\Queue\QueueManager();          
        };   
        // http client  
        $container['http'] = function() {
            return new \GuzzleHttp\Client();
        }; 

        $container->get('view');
        if (Arikaim::isConsole() == false) {
            $container->get('session');
        }
        $container = Self::registerCoreModules($container);

        return $container;
    }
}
