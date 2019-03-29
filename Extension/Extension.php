<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Extension;

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

    private $console_classes = [];

    abstract public function install();
 
    /**
     * Return extension name
     *
     * @return string
     */
    public function getName() 
    {    
        $currentClass = get_class($this);
        $refl = new \ReflectionClass($currentClass);
        $namespace = $refl->getNamespaceName();
        $name = last(explode("\\",$namespace));
        return strtolower($name);
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
     * @param array $args
     * @return object
     */
    public function createJob($class_name, $args = null)
    {
        return Factory::createJob($class_name,$this->getName(),$args);
    }

    /**
     * Register extension event
     *
     * @param string $name Event name
     * @param string $title Event title
     * @param string $description Event description
     * @return void
     */
    public function registerEvent($name, $title = null, $description = null)
    {
        return Arikaim::event()->registerEvent($name,$title,$this->getName(),$description);
    }

    public function getControlerClassName($base_class_name)
    {
        return Factory::getExtensionControlerClass($this->getName(),$base_class_name);
    }
    
    public function addRoute($method, $pattern, $handler_class, $handler_method, $auth = 0, $type = 0)
    {
        $routes = Model::Routes();
        $extension_name = $this->getName();
        $route['method'] = $method;
        $route['pattern'] = $pattern;
        $route['handler_class'] = $this->getControlerClassName($handler_class);
        $route['handler_method'] = $handler_method;
        $route['auth'] = $auth;
        $route['type'] = $type;
        $route['extension_name'] = $extension_name;
        $result = $routes->addRoute($route);
        return $result;
    }

    public function addPageRoute($pattern, $handler_class, $handler_method, $auth = 0)
    {
        $result = $this->addRoute('GET',$pattern,$handler_class,$handler_method,$auth,Routes::TYPE_PAGE);
        return $result;
    }

    public function addApiRoute($method, $pattern, $handler_class, $handler_method, $auth = 0)
    {
        $result = $this->addRoute($method,$pattern,$handler_class,$handler_method,$auth,Routes::TYPE_API);
        return $result;
    }

    public function registerPermission($name, $title = null, $descriptin = null, $parent_id = 0)
    {
        $permission = Model::PermissionsList();
        return $permission->add($name,$this->getName(),$title,$descriptin,$parent_id);
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

    public function onAfterInstall()
    {   
    }

    public function onBeforeInstall()
    {        
    }

    public function onAfterUnInstall()
    {        
    }

    public function onBeforeUnInstall()
    {        
    }

    public function unInstall()
    {        
    }
}
