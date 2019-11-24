<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\App;

use Arikaim\Container\Container;
use Arikaim\Core\Events\EventsManager;
use Arikaim\Core\Db\Model;
use Arikaim\Core\App\Factory;
use Arikaim\Core\Collection\Collection;
use Arikaim\Core\App\Path;
use Arikaim\Core\Packages\Module\ModulePackage;
use Arikaim\Core\System\Config;

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
        $modules = $container->get('cache')->fetch('services.list');
        if (is_array($modules) == false) {
            if ($container->get('db')->isValidPdoConnection() == false) {
                return $container;
            } 
    
            $modules = Model::Modules()->getList(ModulePackage::getTypeId('service'),1);
            $container->get('cache')->save('services.list',$modules,2);    
        } 
        
        foreach ($modules as $module) {
            $serviceName = $module['service_name'];
            if (empty($serviceName) == false) {
                // add to container
                $container[$serviceName] = function() use($module) {
                    return Factory::createModule($module['name'],$module['class']);
                };
            }
            if ($module['bootable'] == true) {
                $container->get($serviceName)->boot();
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
        Config::setConfigDir(Path::CONFIG_PATH);

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
            $routeCacheFile = Path::CACHE_PATH . "/routes.cache.php";            
            return new \Arikaim\Core\Cache\Cache(Path::CACHE_PATH,$routeCacheFile,null,$enabled);
        };
        // Config
        $container['config'] = function($container) {    
            $cache = $container->get('cache');                         
            $config = new \Arikaim\Core\System\Config("config.php",$cache,Path::CONFIG_PATH);         
            return $config;
        }; 
        // Init page components.
        $container['page'] = function($container) {           
            return new \Arikaim\Core\View\Html\Page($container['view']);
        }; 
        // Errors  
        $container['errors'] = function($container) {
            return new \Arikaim\Core\System\Error\Errors($container['page']);          
        };
        // Access
        $container['access'] = function($container) {
            $user = Model::Users();  
            $permissins = Model::PermissionRelations();    
            $access = new \Arikaim\Core\Access\Access($permissins);

            return new \Arikaim\Core\Access\Authenticate($user,$access,$container['errors']);
        };
        // Init template view. 
        $container['view'] = function ($container) {   
            $paths = [Path::EXTENSIONS_PATH,Path::TEMPLATES_PATH,Path::COMPONENTS_PATH];               
            $cache = (isset($container->get('config')['settings']['cache']) == true) ? Path::VIEW_CACHE_PATH : false;
            $debug = (isset($container->get('config')['settings']['debug']) == true) ? $container->get('config')['settings']['debug'] : true;
             
            return new \Arikaim\Core\View\View($paths,['cache' => $cache,'debug' => $debug,'autoescape' => false]);           
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
        $container['options'] = function($container) { 
            return new \Arikaim\Core\Options\Options(null,$container->get('cache'));          
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
        $container = Self::registerCoreModules($container);

        return $container;
    }
}
