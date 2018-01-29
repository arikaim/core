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

class Extension implements ExtensionInterface
{
    const USER_TYPE     = 1;
    const SYSTEM_TYPE   = 2;

    public function __construct() 
    {                
    }
    
    public function getName() {
    
        $currentClass = get_class($this);
        $refl = new \ReflectionClass($currentClass);
        $namespace = $refl->getNamespaceName();
        $name = last(explode("\\",$namespace));
        return $name;
    }

    public function registerEvent($name, $title = "", $description = null)
    {
        $events = Model::Events();

        $event['extension_name'] = $this->getName();
        $event['name'] = $name;
        $event['title'] = $title;
        $event['description'] = $description;
        return $events->addEvent($event);
    }

    public function subscribe($name)
    {
    
    }
    
    public function unsubscribe($name)
    {
        $events_subscribers = Model::EventsSubscribers();
        $extension_name = $this->getName();
    }

    public function addRoute($method, $path, $pattern, $handler_class, $handler_method, $auth = 0, $type = 0)
    {
        $routes = Model::Routes();
        $extension_name = $this->getName();
        $result= $routes->addRoute($method, $path, $pattern, $handler_class, $handler_method, $extension_name, $auth, $type);
        return $result;
    }

    public function addPageRoute($path, $pattern, $handler_class, $handler_method, $auth = 0)
    {
        $routes = Model::Routes();
        $extension_name = $this->getName();
        $result= $routes->addPageRoute($path, $pattern, $handler_class, $handler_method, $extension_name, $auth);
        return $result;
    }

    public function addApiRoute($method, $path, $pattern, $handler_class, $handler_method, $auth = 0)
    {
        $routes = Model::Routes();
        $extension_name = $this->getName();
        $result= $routes->addApiRoute($path, $pattern, $handler_class, $handler_method, $extension_name, $auth);
        return $result;
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

    public function install()
    {        
    }

    public function unInstall()
    {        
    }
}
