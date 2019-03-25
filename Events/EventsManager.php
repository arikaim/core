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

use Arikaim\Core\Arikaim;
use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Events\Event;
use Arikaim\Core\Interfaces\Events\EventInterface;
use Arikaim\Core\Interfaces\Events\EventSubscriberInterface;

/**
 * Manage events
*/
class EventsManager 
{
    private $events;

    public function __construct() 
    {   
        $this->events = [];
    }
    
    public function unregisterEvents($extension_name)
    {
        return Model::Events()->deleteEvents($extension_name);       
    }

    public function unregisterEvent($event_name)
    {
        return Model::Events()->deleteEvent($event_name);
    }

    public function registerEvent($name, $title, $extension_name = null, $description = null)
    {
        if (($this->isCoreEvent($name) == true) && ($extension_name != null)) {
            // core events can't be registered from extension
            return false;
        }
        $event['name'] = $name;
        $event['extension_name'] = $extension_name;
        $event['title'] = $title;
        $event['description'] = $description;
        $result = Model::Events()->addEvent($event);
        return $result;
    }

    public function isCoreEvent($name)
    {
        return (substr($name,0,4) == "core") ? true : false;          
    }

    public function registerSubscriber($base_class_name,$extension_name)
    {
        $subscriber = Factory::createEventSubscriber($base_class_name,$extension_name);
        if ($subscriber != false) {
            $events = $subscriber->getEvents();
            foreach ($events as $event) {
                $this->subscribe($event['event_name'],$base_class_name,$extension_name,$event['priority']);
            }
            return true;
        }
        return false;
    }

    public function subscribe($event_name, $base_class_name, $extension_name, $priority = 0)
    {
        $subscriber['name'] = $event_name;
        $subscriber['priority'] = $priority;
        $subscriber['extension_name'] = $extension_name;
        $subscriber['handler_class'] = Factory::getEventSubscriberClass($base_class_name,$extension_name);
        return Model::EventSubscribers()->add($subscriber);
    }

    public function unsubscribe($event_name, $extension_name)
    {
        return Model::EventSubscribers()->deleteSubscriber($event_name,$extension_name);
    }

    public function trigger($event_name,$event = null)
    {
        if ($event == null) {
            $event = new Event();       
        }
        if (is_array($event) == true) {
            $event = new Event($event);           
        }        
        if ($event instanceof EventInterface == false) {            
            throw new \Exception("Not valid event object");
        }
        $event->setParameter('event_name',$event_name);          

        // load event handlers from database
        $result = Model::Events()->hasEvent($event_name,1);
        if ($result == false) {
            // event not registered or disabled
            Arikaim::logger()->info("Event '$event_name' not registered.");
            return false;
        }
        // get all subscribers for event
        $subscribers = Model::EventSubscribers()->getSubscribers($event_name,1);       
        $result = $this->executeEventHandlers($subscribers,$event);  
        return $result;
    }

    private function executeEventHandlers(array $event_subscribers,Event $event)
    {       
        if (empty($event_subscribers) == true) {
            return false;
        }
        $result = [];
        foreach ($event_subscribers as $item) {
            $subscriber = Factory::createInstance($item['handler_class']);
            if (is_object($subscriber) == true) {
                $event_result = $subscriber->execute($event);
                array_push($result,$event_result);
            }
        }
        return $result;
    }
}
