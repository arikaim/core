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
use Arikaim\Core\Cache\Cache;
use Arikaim\Core\Utils\Path;
use Arikaim\Core\App\TwigExtension;
use Arikaim\Core\Packages\PackageManagerFactory;
use Arikaim\Core\Routes\Routes;
use Arikaim\Core\App\Install;
use Arikaim\Core\View\Html\Page;
use PDOException;

/**
 * Create system services
 */
class ServiceContainer
{
    /**
     * Init default services
     *
     * @param boolean $cosole
     * @return Container
     */
    public static function init($container, $console = false)
    {
        // Cache 
        $container['cache'] = function($container) {                    
            $routeCacheFile = Path::CACHE_PATH . '/routes.cache.php';                   
            return new Cache(Path::CACHE_PATH,$routeCacheFile,Cache::ARRAY_DRIVER,true);
        };
        // Config
        $container['config'] = function($container) {    
            $cache = $container->get('cache');                         
            $config = new \Arikaim\Core\System\Config('config.php',$cache,Path::CONFIG_PATH);         
            return $config;
        }; 
        $cacheStatus = (bool)$container->get('config')->getByPath('settings/cache',false);

        // init cache status
        $container->get('cache')->setStatus($cacheStatus);
        $container->get('cache')->setDriver($container->get('config')->getByPath('settings/cacheDriver',Cache::FILESYSTEM_DRIVER));

        // Events manager 
        $container['event'] = function() {
            return new EventsManager(Model::Events(),Model::EventSubscribers());
        };
        // Storage
        $container['storage'] = function($container) {
            return new \Arikaim\Core\Storage\Storage();
        };
        // Http client  
        $container['http'] = function() {
            return new \Arikaim\Core\Http\HttpClient();
        }; 
        // Package manager factory
        $container['packages'] = function ($container) {     
            return new PackageManagerFactory($container['cache'],$container['storage'],$container['http']);          
        };
        // Init template view. 
        $container['view'] = function ($container) use($cacheStatus) {                            
            $cache = ($cacheStatus == true) ? Path::VIEW_CACHE_PATH : false;
            $debug = (isset($container->get('config')['settings']['debug']) == true) ? $container->get('config')['settings']['debug'] : true;
            $demoMode = (isset($container->get('config')['settings']['demo_mode']) == true) ? $container->get('config')['settings']['demo_mode'] : false;
            return new \Arikaim\Core\View\View(
                $container['cache'],
                Path::VIEW_PATH,
                Path::EXTENSIONS_PATH, 
                Path::TEMPLATES_PATH,
                Path::COMPONENTS_PATH, [
                    'cache'      => $cache,
                    'debug'      => $debug,
                    'demo_mode'  => $demoMode,
                    'autoescape' => false
                ]
            );           
        };    
        // Init page components.
        $container['page'] = function($container) {         
            Page::setDefaultLanguage($container->get('options')->get('default.language','en'));                
            return new Page($container->get('view'),$container->get('options')->get('library.params',[]));
        }; 
        // Errors  
        $container['errors'] = function($container) use ($console) {
            $systemErrors = $container->get('config')->loadJsonConfigFile('errors.json');    
            if ($console == true) {
                $consoleErrors = $container->get('config')->loadJsonConfigFile('console-errors.json'); 
                $systemErrors = \array_merge($systemErrors,$consoleErrors);
            }               
            
            return new \Arikaim\Core\System\Error\Errors($container['page'],$systemErrors);          
        };
        // Access
        $container['access'] = function($container) {
            $user = Model::Users();  
            $permissins = Model::PermissionRelations();    
            $access = new \Arikaim\Core\Access\Access($permissins);

            return new \Arikaim\Core\Access\Authenticate($user,$access);
        };
        // Init Eloquent ORM
        $container['db'] = function($container) {  
            try {  
                $relations = $container->get('config')->load('relations.php');
                $db = new \Arikaim\Core\Db\Db($container->get('config')['db'],$relations);
            } catch(PDOException $e) {
                if (Install::isInstalled() == true) {
                    $container->get('errors')->addError('DB_CONNECTION_ERROR');
                } else {
                    $container->get('errors')->addError('NOT_INSTALLED_ERROR');
                }                
            }      
            return $db;
        };     

        $container['db'];

        // Routes
        $container['routes'] = function($container) {            
            return new Routes(Model::Routes(),$container['cache']);  
        };
        // Options
        $container['options'] = function($container) { 
            $options = ($container['db']->hasError() == false) ? Model::Options(): null;
                    
            return new \Arikaim\Core\Options\Options($container->get('cache'),$options);          
        };     
        // Mailer
        $container['mailer'] = function($container) {
            $mailerOptions = $container['options']->searchOptions('mailer.');
            return new \Arikaim\Core\Mail\Mailer($mailerOptions,$container['page']);
        };
        // Drivers
        $container['driver'] = function() {   
            return new \Arikaim\Core\Driver\DriverManager(Model::Drivers());  
        };
        // Logger
        $container['logger'] = function($container) {                     
            $logger = new \Arikaim\Core\Logger\Logger(Path::LOGS_PATH);
            if ($container->get('options')->get('logger',true) == false) {
                $logger->disable();
            }
            return $logger;
        };      
        // Jobs queue
        $container['queue'] = function($container) {           
            return new \Arikaim\Core\Queue\QueueManager(Model::Jobs(),$container['event'],$container['options']);          
        };          
        // Modules manager
        $container['modules'] = function($container) {           
            return new \Arikaim\Core\Extension\Modules($container->get('cache'));
        }; 
        // Add twig extension
        $twigExtension = new TwigExtension(BASE_PATH,Path::VIEW_PATH,$container);
        $container->get('view')->addExtension($twigExtension);

        return $container;
    }
}
