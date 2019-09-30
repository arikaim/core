<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Packages\Extension;

use Arikaim\Core\Arikaim;
use Arikaim\Core\Interfaces\ExtensionInterface;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Db\Schema;
use Arikaim\Core\Utils\Factory;
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
    private $console_classes = [];

    /**
     * All extensions should implement install method
     *
     * @return mixed
     */
    abstract public function install();
    
    /**
     * Add relation map for Polymorphic Relations relations
     *
     * @param string $type
     * @param string $model_class
     * @return void
     */
    public function addRelationMap($type,$model_class)
    {
        $relations = Arikaim::config()->load('relations.php');       
        $relations[$type] = Model::getFullClassName($model_class,$this->getName());
        $relations = array_unique($relations);
      
        return Arikaim::config()->save('relations.php',$relations);
    }

    /**
     * Create extension option
     *
     * @param string $key
     * @param [mxied $value
     * @param boolean $auto_load
     * @return bool
     */
    public function createOption($key, $value, $auto_load = true)
    {
        return Arikaim::options()->createOption($key, $value, $auto_load,$this->getName());
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
        $class = Factory::getClassName($this);
        return strtolower($class);      
    }

    /**
     * Return console commands classes
     *
     * @return array
     */
    public function getConsoleCommands()
    {
        return $this->console_classes;
    }

    /**
     * Register console command class
     *
     * @param string $class_name
     * @return bool
     */
    public function registerConsoleCommand($class_name)
    {
        $class_name = Factory::getExtensionConsoleClassName($this->getName(),getClassBaseName($class_name));
        if (class_exists($class_name) == false) {
            return false;
        }
        array_push($this->console_classes,$class_name);
        $console_classes = array_unique($this->console_classes);
        return true;
    }

    /**
     * Create job
     *
     * @param string $class_name
     * @param string|null $name
     * @return JobInterface
     */
    public function createJob($class_name, $name = null)
    {       
        return Factory::createJob($class_name,$this->getName(),$name);
    }

    /**
     * Add job to queue
     *
     * @param string $class_name
     * @param string|null $name
     * @return boolean
     */
    public function addJob($class_name, $name = null)
    {       
        $job = $this->createJob($class_name,$name);
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
     * @param string $base_class_name
     * @return string
     */
    public function getControllerClassName($base_class_name)
    {
        return (Factory::isCoreControllerClass($base_class_name) == true) ? $base_class_name : Factory::getExtensionControllerClass($this->getName(),$base_class_name);       
    }
    
    /**
     * Register route
     *
     * @param string $method
     * @param string $pattern
     * @param string $handler_class
     * @param string $handler_method
     * @param null|integer|string $auth
     * @param integer $type
     * @param string|null $page_name
     * @param string|null $redirect_url 
     * @return bool
     */
    public function addRoute($method, $pattern, $handler_class, $handler_method, $auth = null, $type = 0, $page_name = null, $redirect_url = null)
    {
        $routes = Model::Routes();
        $route = [
            'method'            => $method,
            'pattern'           => $pattern,
            'handler_class'     => $this->getControllerClassName($handler_class),
            'handler_method'    => $handler_method,
            'auth'              => Arikaim::auth()->resolveAuthType($auth),
            'type'              => $type,
            'extension_name'    => $this->getName(),
            'page_name'     => $page_name,
            'redirect_url'      => $redirect_url
        ];
    
        return $routes->addRoute($route);      
    }

    /**
     * Register page route
     *
     * @param string $pattern
     * @param string|null $handler_class
     * @param string|null $handler_method
     * @param null|integer|string $auth
     * @param string|null $redirect_url 
     * @return bool
     */
    public function addPageRoute($pattern, $page_name, $handler_class = null, $handler_method = null, $auth = null, $redirect_url = null)
    {
        $routes = Model::Routes();
        $handler_class = ($handler_class == null) ? Factory::getControllerClass("PageLoader") : $this->getControllerClassName($handler_class);
        $handler_method = ($handler_method == null) ? "loadPage" : $handler_method;
        $auth = Arikaim::auth()->resolveAuthType($auth);

        return $routes->addPageRoute($pattern,$handler_class,$handler_method,$this->getName(),$page_name,$auth,$redirect_url);
    }

    /**
     * Add page error route
     *
     * @param string $pattern
     * @param string $page_name
     * @param string|null $handler_class
     * @param string|null $handler_method
     * @param string|null $redirect_url 
     * @return boolean
     */
    public function addErrorRoute($pattern, $page_name, $redirect_url = null, $handler_class = null, $handler_method = null)
    {
        return $this->addPageRoute($pattern,$page_name,null,$handler_class,$handler_method,Routes::TYPE_ERROR_PAGE,$redirect_url);
    }

    /**
     * Add auth error page route
     *
     * @param string $pattern
     * @param string $page_name
     * @param string|null $redirect_url 
     * @param string|null $handler_class
     * @param string|null $handler_method
     * @return boolean
     */
    public function addAuthErrorRoute($pattern, $page_name, $auth = null, $redirect_url = null, $handler_class = null, $handler_method = null)
    {
        return $this->addPageRoute($pattern,$page_name,$auth,$handler_class,$handler_method,Routes::TYPE_AUTH_ERROR_PAGE,$redirect_url);
    }

    /**
     * Register api route 
     *
     * @param string $method
     * @param string $pattern
     * @param string $handler_class
     * @param string $handler_method
     * @param null|integer|string $auth
     * @return bool
     */
    public function addApiRoute($method, $pattern, $handler_class, $handler_method, $auth = null)
    {
        return $this->addRoute($method,$pattern,$handler_class,$handler_method,$auth,Routes::TYPE_API);
    }

    /**
     * Creaete extensin db table 
     *
     * @param string $schema_class
     * @return boolean
     */
    public function createDbTable($schema_class)
    {
        $extension_name = $this->getName();
        return Schema::install($schema_class,$extension_name);
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
