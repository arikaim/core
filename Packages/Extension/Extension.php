<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Packages\Extension;

use Arikaim\Core\Arikaim;
use Arikaim\Core\Interfaces\ExtensionInterface;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Db\Schema;
use Arikaim\Core\App\Factory;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Models\Routes;

/**
 * Base class for all extensions.
*/
abstract class Extension implements ExtensionInterface
{
    /**
     * Undocumented variable
     *
     * @var array
     */
    private $consoleClasses = [];

    /**
     * All extensions should implement install method
     *
     * @return mixed
     */
    abstract public function install();
    
    /**
     * UnInstall extension
     *
     * @return boolean
     */
    public function unInstall()
    {
        return true;
    }

    /**
     * Add permission item
     *
     * @param string $name
     * @param string|null $title
     * @param string|null $description
     * @return boolean
     */
    public function addPermission($name, $title = null, $description = null)
    {
        $model = Model::Permissions()->add($name,$title,$description,$this->getName());
        
        return is_object($model);
    }

    /**
     * Add relation map for Polymorphic Relations relations
     *
     * @param string $type
     * @param string $modelClass
     * @return void
     */
    public function addRelationMap($type, $modelClass)
    {
        $relations = Arikaim::config()->load('relations.php');       
        $relations[$type] = Factory::getModelClass($modelClass,$this->getName());
        $relations = array_unique($relations);
      
        return Arikaim::config()->save('relations.php',$relations);
    }

    /**
     * Create extension option
     *
     * @param string $key
     * @param mxied $value
     * @param boolean $autoLoad
     * @return bool
     */
    public function createOption($key, $value, $autoLoad = true)
    {
        return Arikaim::options()->createOption($key, $value, $autoLoad,$this->getName());
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
    public function installDriver($name, $class = null, $category = null, $title = null, $description = null, $version = null, $config = [])
    {
        return Arikaim::driver()->install($name,$class,$category,$title,$description,$version,$config,$this->getName());
    }

    /**
     * Return extension name
     *
     * @return string
     */
    public function getName() 
    {    
        $class = Utils::getBaseClassName($this);
        
        return strtolower($class);      
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
     * Register console command class
     *
     * @param string $class
     * @return bool
     */
    public function registerConsoleCommand($class)
    {
        $class = Factory::getExtensionConsoleClassName($this->getName(),Utils::getBaseClassName($class));
        if (class_exists($class) == false) {
            return false;
        }
        array_push($this->consoleClasses,$class);
        $consoleClasses = array_unique($this->consoleClasses);

        return true;
    }

    /**
     * Create job
     *
     * @param string $class
     * @param string|null $name
     * @return JobInterface
     */
    public function createJob($class, $name = null)
    {       
        return Factory::createJob($class,$this->getName(),$name);
    }

    /**
     * Add job to queue
     *
     * @param string $class
     * @param string|null $name
     * @return boolean
     */
    public function addJob($class, $name = null)
    {       
        $job = $this->createJob($class,$name);
        if (is_object($job) == false) {
            return false;
        }

        return Arikaim::queue()->addJob($job,$this->getName());
    }

    /**
     * Register extension event
     *
     * @param string $name Event name
     * @param string $title Event title
     * @param string $description Event description
     * @return bool
     */
    public function registerEvent($name, $title = null, $description = null)
    {
        return Arikaim::event()->registerEvent($name,$title,$this->getName(),$description);
    }

    /**
     * Get extension controller full class name
     *
     * @param string $class
     * @return string
     */
    public function getControllerClassName($class)
    {
        return (Factory::isCoreControllerClass($class) == true) ? $class : Factory::getExtensionControllerClass($this->getName(),$class);       
    }
    
    /**
     * Register route
     *
     * @param string $method
     * @param string $pattern
     * @param string $class
     * @param string $handlerMethod
     * @param null|integer|string $auth
     * @param integer $type
     * @param string|null $pageName
     * @param string|null $redirectUrl 
     * @return bool
     */
    public function addRoute($method, $pattern, $class, $handlerMethod, $auth = null, $type = 0, $pageName = null, $redirectUrl = null)
    {
        $routes = Model::Routes();
        $route = [
            'method'            => $method,
            'pattern'           => $pattern,
            'handler_class'     => $this->getControllerClassName($class),
            'handler_method'    => $handlerMethod,
            'auth'              => Arikaim::auth()->resolveAuthType($auth),
            'type'              => $type,
            'extension_name'    => $this->getName(),
            'page_name'         => $pageName,
            'redirect_url'      => $redirectUrl
        ];
    
        return $routes->addRoute($route);      
    }

    /**
     * Register page route
     *
     * @param string $pattern
     * @param string|null $class
     * @param string|null $handlerMethod
     * @param null|integer|string $auth
     * @param string|null $redirectUrl 
     * @param string|null $routeName
     * @param boolean $withLanguage
     * @return bool
     */
    public function addPageRoute($pattern, $class = null, $handlerMethod = null, $auth = null, $redirectUrl = null, $routeName = null, $withLanguage = true)
    {
        $routes = Model::Routes();
        $class = ($class == null) ? Factory::getControllerClass("PageLoader") : $this->getControllerClassName($class);
        $handlerMethod = ($handlerMethod == null) ? "loadPage" : $handlerMethod;
        $auth = Arikaim::auth()->resolveAuthType($auth);

        return $routes->addPageRoute($pattern,$class,$handlerMethod,$this->getName(),null,$auth,$redirectUrl,$routeName,$withLanguage);
    }

    /**
     * Register show page route (handler: PageLoader:loadPage)
     *
     * @param string $pattern
     * @param string $pageName
     * @param null|integer|string $auth
     * @param string|null $redirectUrl 
     * @param string|null $routeName
     * @param boolean $withLanguage
     * @return bool
     */

    public function addShowPageRoute($pattern, $pageName, $auth = null, $redirectUrl = null, $routeName = null, $withLanguage = true)
    {
        $routes = Model::Routes();       
        $auth = Arikaim::auth()->resolveAuthType($auth);

        return $routes->addPageRoute($pattern,Factory::getControllerClass("PageLoader"),"loadPage",$this->getName(),$pageName,$auth,$redirectUrl,$routeName,$withLanguage);
    }

    /**
     * Add page error route
     *
     * @param string $pattern
     * @param string $pageName
     * @param string|null $class
     * @param string|null $handlerMethod
     * @param string|null $redirectUrl 
     * @return boolean
     */
    public function addErrorRoute($pattern, $pageName, $redirectUrl = null, $class = null, $handlerMethod = null)
    {
        return $this->addPageRoute($pattern,$pageName,null,$class,$handlerMethod,Routes::TYPE_ERROR_PAGE,$redirectUrl);
    }

    /**
     * Add auth error page route
     *
     * @param string $pattern
     * @param string $pageName
     * @param string|null $redirectUrl 
     * @param string|null $class
     * @param string|null $handlerMethod
     * @return boolean
     */
    public function addAuthErrorRoute($pattern, $pageName, $auth = null, $redirectUrl = null, $class = null, $handlerMethod = null)
    {
        return $this->addPageRoute($pattern,$pageName,$auth,$class,$handlerMethod,Routes::TYPE_AUTH_ERROR_PAGE,$redirectUrl);
    }

    /**
     * Register api route 
     *
     * @param string $method
     * @param string $pattern
     * @param string $class
     * @param string $handlerMethod
     * @param null|integer|string $auth
     * @return bool
     */
    public function addApiRoute($method, $pattern, $class, $handlerMethod, $auth = null)
    {
        return $this->addRoute($method,$pattern,$class,$handlerMethod,$auth,Routes::TYPE_API);
    }

    /**
     * Creaete extension db table 
     *
     * @param string $schemaClass
     * @return boolean
     */
    public function createDbTable($schemaClass)
    {       
        return Schema::install($schemaClass,$this->getName());
    }

    /**
     * Drop extension db table
     *
     * @param string $schemaClass
     * @return boolean
     */
    public function dropDbTable($schemaClass)
    {
        return Schema::unInstall($schemaClass,$this->getName());
    }

    /**
     * Executed after install extension.
     *
     * @return void
     */
    public function onAfterInstall()
    {   
    }

    /**
     * Executed before install extension.
     *
     * @return void
     */
    public function onBeforeInstall()
    {        
    }

    /**
     * Executed after uninstall extension.
     *
     * @return void
     */
    public function onAfterUnInstall()
    {        
    }

    /**
     * Executed before uninstall extension.
     *
     * @return void
     */
    public function onBeforeUnInstall()
    {        
    }
}
