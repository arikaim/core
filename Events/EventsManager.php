<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Events;

use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Events\AbstractEvent;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Interfaces\EventManagerInterface;

class EventsManager 
{
    private $events;

    public function __construct() 
    {   
        $this->events = [];
    }
    
    public function attach($event, $callback, $priority = 0)
    {
        if (is_array($this->events[$event]) == false) {
            $this->events[$event] = [];
        }  
        array_push($this->events[$event],$callback);
    }
    
    public function detach($event, $callback)
    {
        if (is_array($this->events[$event]) == true) {
            $key = array_search($callback,$this->events[$event]);
            if ($key != false) {
                unset($this->events[$event][$key]);
            }
        }

    }

    public function clearListeners($event)
    {
        $this->events[$event] = [];
    }

    public function trigger($event_name, $args = [])
    {
        $count = 0;
        // execute registered  eventg handlers
        if (array_key_exists($event_name,$this->events) == true ) {
            $count += $this->executeEventHandlers($this->events[$event_name],$args);
        }
        // load event handlers from database
        $events = Model::Events()->getEvents($event_name);
        $count += $this->executeEventHandlers($events,$args);  

        return $count;
    }

    private function executeEventHandlers(array $event_handlers, $args = [])
    {       
        if (empty($event_handlers) == true) {
            return false;
        }
        $executed = 0;
        foreach ($event_handlers as $handler) {
            $event = Factory::createInstance($handler['handler_class']);
            if ( is_object($event) == true ) {
                $event->action($args);
                $executed++;
            }
        }
        return $executed;
    }
    
    public static function createEvent($base_class_name, $extension_name = null)
    {
        if ($extension_name != null) {
            $class_name = EventsManager::getExtensionEventClass($extension_name,$base_class_name);
        } else {
            $class_name = EventsManager::getSystemEventsNamespace();
        }       
        $instance = Factory::createInstance($class_name);
        if (is_subclass_of($instance,'Arikaim\Core\Events\EventListener') == true) {  
            return $instance;
        }
        return false;
    }

    public static function getExtensionEventClass($extension_name, $base_class_name)
    {
        return EventsManager::getExtensionEventsNamespace($extension_name) . "\\" . $base_class_name;
    }

    public static function getExtensionEventsNamespace($extension_name)
    {
        $extension_name = ucfirst($extension_name);
        return "\\Arikaim\\Extensions\\$extension_name\\Events";
    }

    public static function getSystemEventsNamespace()
    {
        return "\\Arikaim\\Core\\Events";
    }
}
