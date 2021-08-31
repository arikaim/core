<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Extension;

use Arikaim\Core\Interfaces\ExtensionInterface;
use Arikaim\Core\Interfaces\RoutesInterface;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Db\Schema;
use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Utils\File;
use Arikaim\Core\Utils\Path;
use Arikaim\Core\Routes\Route;
use Arikaim\Core\Routes\RouteType;

use Arikaim\Core\System\Error\Traits\TaskErrors;

/**
 * Base class for all extensions.
*/
abstract class Extension implements ExtensionInterface
{
    use TaskErrors;

    /**
     * Primary extension
     *
     * @var boolean
     */
    private $primary;

    /**
     * Extension console classes 
     *
     * @var array
     */
    private $consoleClasses = [];

    /**
     * All extensions should implement install method
     *
     * @return void
     */
    abstract public function install();

    /**
     * Constructor
     */
    public function __construct() 
    {       
        $this->primary = false;
    }

    /**
     * Register content provider
     *
     * @param string|object $provider
     * @return boolean
     */
    public function registerContentProvider($provider): bool
    {
        if (\is_string($provider) == true) {
            $class = (\class_exists($provider) == true) ? $provider : Factory::getExtensionClassName($this->getName(),$provider); 
            $provider = new $class();
        }
        return Arikaim::get('content')->registerProvider($provider);
    }

    /**
     * Register content type action
     *
     * @param string|object $provider
     * @return boolean
     */
    public function registerContentTypeAction(string $contentType, string $class): bool
    {
        $class = (\class_exists($class) == true) ? $class : Factory::getExtensionClassName($this->getName(),$class); 
         
        if (\class_exists($class) == false) {
            return false;
        }

        return Arikaim::get('content')->typeRegistry()->registerAction($contentType,$class);
    }

    /**
     * Register content type
     *
     * @param string $class
     * @return boolean
     */
    public function registerContentType(string $class): bool 
    {
        $class = (\class_exists($class) == true) ? $class : Factory::getExtensionClassName($this->getName(),$class);       
        if (\class_exists($class) == false) {
            return false;
        }
        $contentType = new $class();

        return Arikaim::get('content')->typeRegistry()->register($contentType);
    }

    /**
     * Remove content provider
     *
     * @param string|object $provider
     * @return boolean
     */
    public function unRegisterContentProvider($provider): bool
    {
        return Arikaim::get('content')->unRegisterProvider($provider);
    }

    /**
     * Return true if extension exist
     *
     * @param string $name
     * @return boolean
     */
    public function hasExtension(string $name): bool
    {
        return Arikaim::get('packages')->create('extension')->hasPackage($name);
    }

    /**
     * Return true if extension is installed
     *
     * @param string $name
     * @return boolean
     */
    public function hasInstalledExtension(string $name): bool
    {
        $package = Arikaim::get('packages')->create('extension')->createPackage($name);

        return (\is_object($package) == true) ? $package->isInstalled() : false;
    }

    /**
     * Return true if module exist
     *
     * @param string $name
     * @return boolean
    */
    public function hasModule(string $name): bool
    {
        return Arikaim::get('packages')->create('modules')->hasPackage($name);
    }

    /**
     * Call function
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
    }

    /**
     * UnInstall extension
     *
     * @return void
     */
    public function unInstall()
    {
    }

    /**
     * Run post install actions
     *
     * @return void
     */
    public function postInstall()
    {        
    }

    /**
     * Call methods
     *
     * @param string $baseClass
     * @param string $extension
     * @param Closure $callback
     * @return mixed|false
     */
    public static function run(string $baseClass, string $extension, $callback) 
    {
        $class = Factory::getExtensionClassName($extension,$baseClass);
        $instance = Factory::createInstance($class);
    
        if (\is_object($instance) == true) {
            return (\is_callable($callback) == true) ? $callback($instance) : false;
        }

        return false;
    }

    /**
     * Load extension json config file
     *
     * @param string $fileName
     * @param string $extensionName
     * @return array
     */
    public static function loadJsonConfigFile(string $fileName, string $extensionName)
    {
        $configDir = Path::EXTENSIONS_PATH . $extensionName . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;
        $data = File::readJsonFile($configDir . $fileName);
        
        return (\is_array($data) == true) ? $data : [];
    }

    /**
     * Create extension storage folder
     *
     * @param string|null $dir
     * @param boolean $public
     * @return bool
     */
    public function createStorageFolder(?string $dir = null, bool $public = false)
    {
        $path = ($public == true) ? Path::STORAGE_PUBLIC_PATH : Path::STORAGE_PATH;
        $dir = $dir ?? $this->getName();
        $storagePath = $path . $dir . DIRECTORY_SEPARATOR;

        if (File::exists($storagePath) == false) {
            File::makeDir($storagePath);
        }

        $result = File::exists($storagePath);
        if ($result === false) {
            // add error
            $this->addError(Arikaim::errors()->getError('EXTENSION_STORAGE_FOLDER',['path' => $storagePath]));
        } 
        
        return $result;
    }
    
    /**
     * Set extension as primary (override all existing routes)
     *
     * @return void
     */
    public function setPrimary()
    {
        $this->primary = true;
    }

    /**
     * Return true if extension is primary
     *
     * @return boolean
     */
    public function isPrimary()
    {
        return ($this->primary == true);
    }

    /**
     * Add permission item
     *
     * @param string $name
     * @param string|null $title
     * @param string|null $description
     * @param bool|null $deny
     * @return boolean
     */
    public function addPermission(string $name, ?string $title = null, ?string $description = null, ?bool $deny = false)
    {
        return Arikaim::access()->addPermission($name,$title,$description,$this->getName(),$deny);            
    }

    /**
     * Add relations map
     *
     * @param array $items
     * @return boolean
     */
    public function addRelationsMap(array $items): bool
    {
        $relations = Arikaim::config()->load('relations.php',false);  
        
        foreach ($items as $key => $value) {
           $relations[$key] = Factory::getModelClass($value,$this->getName());
        }

        $relations = \array_unique($relations);
        $result = Arikaim::config()->save('relations.php',$relations);
        if ($result === false) {
            // add error
            $this->addError(Arikaim::errors()->getError('REGISTER_RELATION_ERROR'));
        }   

        return $result;
    }

    /**
     * Add relation map for Polymorphic Relations relations
     *
     * @param string $type
     * @param string $modelClass
     * @return bool
     */
    public function addRelationMap(string $type, string $modelClass): bool
    {      
        $relations = Arikaim::config()->load('relations.php',false);  
        $relations = \array_unique($relations);

        $relations[$type] = Factory::getModelClass($modelClass,$this->getName());
      
        $result = Arikaim::config()->save('relations.php',$relations);
        if ($result === false) {
            // add error
            $this->addError(Arikaim::errors()->getError('REGISTER_RELATION_ERROR',['class' => $modelClass]));
        }   

        return $result;
    }

    /**
     * Create extension option
     *
     * @param string $key
     * @param mixed $value
     * @param boolean $autoLoad
     * @return bool
     */
    public function createOption(string $key, $value, bool $autoLoad = true): bool
    {
        $result = Arikaim::options()->createOption($key,$value,$autoLoad,$this->getName());
        if ($result !== true) {
            if (Arikaim::options()->has($key) == false) {
                // add error
                $this->addError(Arikaim::errors()->getError('ERROR_CREATE_OPTION',['key' => $key]));
                return false;
            }   
            return true;
        } 
        
        return $result;
    }

    /**
      * Install driver
      *
      * @param string|object $name Driver name, full class name or driver object ref
      * @param string|null $class
      * @param string|null $category
      * @param string|null $title
      * @param string|null $description
      * @param string|null $version
      * @param array $config
      * @return boolean|Model
    */
    public function installDriver(
        $name, 
        ?string $class = null, 
        ?string $category = null, 
        ?string $title = null, 
        ?string $description = null, 
        ?string $version = null, 
        array $config = []
    )
    {
        $result = Arikaim::driver()->install($name,$class,$category,$title,$description,$version,$config,$this->getName());
        if ($result !== true) {
            // add error
            $this->addError(Arikaim::errors()->getError('ERROR_INSTALL_DRIVER',['name' => $name]));
        } 
        
        return $result;
    }

    /**
     * Uninstall driver
     *
     * @param string $name Driver name   
     * @return boolean
    */
    public function unInstallDriver(string $name)
    {
        $result = Arikaim::driver()->unInstall($name);
        if ($result !== true) {
            // add error
            $this->addError(Arikaim::errors()->getError('ERROR_UNINSTALL_DRIVER',['name' => $name]));
        } 
        
        return $result;
    }

    /**
     * Return extension name
     *
     * @return string
     */
    public function getName() 
    {    
        $class = Utils::getBaseClassName($this);
        
        return \strtolower($class);      
    }

    /**
     * Return console commands classes
     *
     * @return array
     */
    public function getConsoleCommands()
    {
        return $this->consoleClasses;
    }

    /**
     * Register service provider
     *
     * @param string $serviceProvider
     * @return boolean
     */
    public function registerService(string $serviceProvider): bool
    {
        if (\class_exists($serviceProvider) == false) {
            $serviceProvider = Factory::getExtensionNamespace($this->getName()) . "\\Service\\$serviceProvider";
        }
      
        $result = Arikaim::get('service')->register($serviceProvider);
        if ($result == false) {
            $error = Arikaim::errors()->getError('NOT_VALID_SERVICE_CLASS',['class' => $serviceProvider]);
            $this->addError($error);
        }

        return $result;
    }

    /**
     * UnRegister service provider
     *
     * @param string $serviceProvider
     * @return boolean
     */
    public function unRegisterService(string $serviceProvider): bool
    {
        if (\class_exists($serviceProvider) == false) {
            $serviceProvider = Factory::getExtensionNamespace($this->getName()) . "\\Service\\$serviceProvider";
        }
      
        $result = Arikaim::get('service')->unRegister($serviceProvider);
        if ($result == false) {
            $error = Arikaim::errors()->getError('NOT_VALID_SERVICE_CLASS',['class' => $serviceProvider]);
            $this->addError($error);
        }

        return $result;
    }

    /**
     * Register console command class
     *
     * @param string $class
     * @return bool
     */
    public function registerConsoleCommand(string $class)
    {
        $class = Factory::getExtensionConsoleClassName($this->getName(),Utils::getBaseClassName($class));
        if (\class_exists($class) == false) {
            // add error
            $this->addError(Arikaim::errors()->getError('NOT_VALID_CONSOLE_CLASS',['class' => $class])); 
            return false;
        }
        \array_push($this->consoleClasses,$class);
        $this->consoleClasses = \array_unique($this->consoleClasses);

        return true;
    }

    /**
     * Add job to queue
     *
     * @param string $class
     * @param string|null $name
     * @param bool $disabled
     * @return boolean
     */
    public function addJob(string $class, ?string $name = null, bool $disabled = false)
    {       
        $job = Factory::createJob($class,$this->getName(),$name);

        if (\is_object($job) == false) {
            $this->addError(Arikaim::errors()->getError('REGISTER_JOB_ERROR',['name' => $name])); 

            return false;
        }

        $result = Arikaim::queue()->addJob($job,$this->getName(),$disabled);
        if ($result !== true) {
            $this->addError(Arikaim::errors()->getError('REGISTER_JOB_ERROR',['name' => $name])); 
        }

        return $result;
    }

    /**
     * Register extension event
     *
     * @param string $name Event name
     * @param string|null $title Event title
     * @param string|null $description Event description
     * @return bool
     */
    public function registerEvent(string $name, ?string $title = null, ?string $description = null)
    {
        $result = Arikaim::event()->registerEvent($name,$title,$this->getName(),$description);
        if ($result !== true) {
            $this->addError(Arikaim::errors()->getError('REGISTER_EVENT_ERROR',['name' => $name])); 
        }

        return $result;
    }

    /**
     * Get extension controller full class name
     *
     * @param string $class
     * @return string
     */
    public function getControllerClassName(string $class)
    {
        return ((\substr($class,0,7) == 'Arikaim') == true) ? $class : Factory::getExtensionControllerClass($this->getName(),$class);       
    }

    /**
     * Register home page route
     *
     * @param string $pattern
     * @param string|null $class
     * @param string|null $handlerMethod
     * @param null|integer|string $auth
     * @param string|null $pageName
     * @param string|null $routeName
     * @param boolean $withLanguage    
     * @return bool
     */
    public function addHomePageRoute(
        string $pattern, 
        ?string $class = null, 
        ?string $handlerMethod = null, 
        ?string $pageName = null, 
        $auth = null, 
        ?string $routeName = null, 
        bool $withLanguage = false
    )
    {       
        if ($this->primary == true) {                      
            Arikaim::routes()->deleteHomePage();                     
        } else {
            // find home page route
            $homePageRoute = Arikaim::routes()->getRoutes(['type' => 3]);
            if (empty($homePageRoute) == false) {
                return true;
            }          
        }

        return $this->addPageRoute($pattern,$class,$handlerMethod,$pageName,$auth,$routeName,$withLanguage,3);
    }

    /**
     * Register page route
     *
     * @param string $pattern
     * @param string|null $class
     * @param string|null $handlerMethod
     * @param null|integer|string $auth
     * @param string|null $pageName
     * @param string|null $routeName
     * @param boolean $withLanguage
     * @param integer $type
     * @return bool
     */
    public function addPageRoute(
        string $pattern, 
        ?string $class = null, 
        ?string $handlerMethod = null, 
        ?string $pageName = null, 
        $auth = null, 
        ?string $routeName = null, 
        bool $withLanguage = false, 
        int $type = 1
    )
    {
        $class = ($class == null) ? Factory::getControllerClass('Controller') : $this->getControllerClassName($class);
        $handlerMethod = ($handlerMethod == null) ? 'pageLoad' : $handlerMethod;
        $auth = Arikaim::access()->resolveAuthType($auth);
        
        // if extension is primary remove existing page route
        if ($this->isPrimary() == true) {
            Arikaim::routes()->delete('GET',$pattern);          
            Arikaim::routes()->delete('GET',$pattern . Route::getLanguagePattern($pattern));
        }

        $result = Arikaim::routes()->addPageRoute($pattern,$class,$handlerMethod,$this->getName(),$pageName,$auth,$routeName,$withLanguage,$type);
        if ($result !== true) {           
            $this->addError(Arikaim::errors()->getError('REGISTER_ROUTE_ERROR',['pattern' => $pattern])); 
        }

        return $result;
    }

    /**
     * Register show page route
     *
     * @param string $pattern
     * @param string $pageName
     * @param null|integer|string $auth
     * @param string|null $routeName
     * @param boolean $withLanguage
     * @return bool
     */
    public function addShowPageRoute(string $pattern, string $pageName, $auth = null, bool $withLanguage = true, ?string $routeName = null)
    {                  
        return $this->addPageRoute($pattern,null,'pageLoad',$pageName,$auth,$routeName,$withLanguage);
    }

    /**
     * Set route redirect url
     *
     * @param string $method
     * @param string $pattern
     * @param string $url
     * @return boolean
    */
    public function setRouteRedirectUrl(string $method, string $pattern, string $url)
    {
        return Arikaim::routes()->setRedirectUrl($method,$pattern,$url);
    }

    /**
     * Add middleware
     *
     * @param string $method
     * @param string $pattern
     * @param mixed $class
     * @param string|null $moduleName
     * @return bool
     */
    public function addMiddleware(string $method, string $pattern, $class, ?string $moduleName = null): bool
    {
        if (\is_object($class) == true) {
            $class = \get_class($class);
        }

        if (\class_exists($class) == false) {
            $class = Factory::getModuleClass($moduleName,$class);        
        }

        if (\class_exists($class) == false) {
            // not valid middleware class
            $this->addError('Not valid middleware class ' . $class); 
            return false;
        }
        
        return Arikaim::routes()->addMiddleware($method,$pattern,$class);
    }

    /**
     * Register api route 
     *
     * @param string $method
     * @param string $pattern
     * @param string $class
     * @param string $handlerMethod
     * @param null|integer|string $auth
     * @param int|null $type
     * @return bool
     */
    public function addApiRoute(
        string $method,
        string $pattern, 
        string $class, 
        string $handlerMethod, 
        $auth = null,
        ?int $type = null
    )
    {
        $auth = Arikaim::access()->resolveAuthType($auth);   
        $class = ($class == null) ? Factory::getControllerClass('Controller') : $this->getControllerClassName($class);
        
        // resolve api type
        if (empty($type) == true) {           
            $type = (RouteType::getType($pattern) == RouteType::ADMIN_API_URL) ? RoutesInterface::ADMIN_API : RoutesInterface::API; 
        }
     
        $result = Arikaim::routes()->addApiRoute($method,$pattern,$class,$handlerMethod,$this->getName(),$auth,$type);

        if ($result !== true) {
            $this->addError(Arikaim::errors()->getError('REGISTER_ROUTE_ERROR',['pattern' => $pattern])); 
        }

        return $result;
    }

    /**
     * Register control panel api route 
     *
     * @param string $method
     * @param string $pattern
     * @param string $class
     * @param string $handlerMethod
     * @param null|integer|string $auth
     * @return bool
     */
    public function addAdminApiRoute(
        string $method,
        string $pattern, 
        string $class, 
        string $handlerMethod, 
        $auth = 'session'
    )
    {
        return $this->addApiRoute($method,$pattern,$class,$handlerMethod,$auth,RoutesInterface::ADMIN_API);
    }

    /**
     * Creaete extension db table 
     *
     * @param string $schemaClass
     * @return boolean
     */
    public function createDbTable(string $schemaClass)
    {       
        $result = Schema::install($schemaClass,$this->getName());   
        if ($result !== true) {
            $this->addError(Arikaim::errors()->getError('CREATE_DB_TABLE_ERROR',['class' => $schemaClass])); 
        }

        return $result;
    }

    /**
     * Drop extension db table
     *
     * @param string $schemaClass
     * @return boolean
     */
    public function dropDbTable(string $schemaClass)
    {
        $result = Schema::unInstall($schemaClass,$this->getName());
        if ($result !== true) {
            $this->addError(Arikaim::errors()->getError('DROP_DB_TABLE_ERROR',['class' => $schemaClass])); 
        }

        return $result;
    }
}
