<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Events;

use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Events\Event;
use Arikaim\Core\Interfaces\Events\EventInterface;
use Arikaim\Core\Interfaces\Events\EventSubscriberInterface;

/**
 * Dispatch and manage events and event subscribers.
*/
class EventsManager 
{
    /**
     * Subscribers
     *
     * @var array
     */
    protected $subscribers;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->subscribers = [];
    }
    
    /**
     * Unregister events for extension (removes events from db table)
     *
     * @param string $extension_name
     * @return void
     */
    public function unregisterEvents($extension_name)
    {
        return Model::Events()->deleteEvents($extension_name);       
    }

    /**
     * Unregister event
     *
     * @param string $event_name
     * @return bool
     */
    public function unregisterEvent($event_name)
    {
        return Model::Events()->deleteEvent($event_name);
    }

    /**
     * Add event to events db table.
     *
     * @param string $name
     * @param string $title
     * @param string $extension_name
     * @param string $description
     * @return bool
     */
    public function registerEvent($name, $title, $extension_name = null, $description = null)
    {
        if (($this->isCoreEvent($name) == true) && ($extension_name != null)) {
            // core events can't be registered from extension
            return false;
        }
        $event = [
            'name'           => $name,
            'extension_name' => $extension_name,
            'title'          => $title,
            'description'    => $description
        ];
        return Model::Events()->addEvent($event);
    }

    /**
     * Check if event name is core event name.
     *
     * @param string $name
     * @return boolean
     */
    public function isCoreEvent($name)
    {
        return (substr($name,0,4) == "core") ? true : false;          
    }

    /**
     * Register event subscriber.
     *
     * @param string $base_class_name
     * @param string $extension_name
     * @return bool
     */
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

    /**
     * Save subscriber info to db table. 
     *
     * @param string $event_name
     * @param string $base_class_name
     * @param string $extension_name
     * @param integer $priority
     * @return bool
     */
    public function subscribe($event_name, $base_class_name, $extension_name, $priority = 0)
    {
        $subscriber = [
            'name'           => $event_name,
            'priority'       => $priority,
            'extension_name' => $extension_name,
            'handler_class'  => Factory::getEventSubscriberClass($base_class_name,$extension_name)
        ];
        return Model::EventSubscribers()->add($subscriber);
    }

    /**
     * Subscribe callback
     *
     * @param string $event_name
     * @param Closure $callback
     * @param boolean $single
     * @return void
     */
    public function subscribeCallback($event_name, $callback, $single = false)
    {        
        if (isset($this->subscribers[$event_name]) == false) {
            $this->subscribers[$event_name] = [];
        }
        if ($single == true) {
            $this->subscribers[$event_name] = [$callback];
        } else {
            array_push($this->subscribers[$event_name],$callback);
        }
    }

    /**
     * Remove event subscribers
     *
     * @param string $event_name
     * @param string $extension_name
     * @return bool
     */
    public function unsubscribe($event_name, $extension_name)
    {
        return Model::EventSubscribers()->deleteSubscribers($event_name,$extension_name);
    }

    /**
     * Fire event, dispatch event data to all subscribers
     *
     * @param string $event_name
     * @param array|EventInterface $event
     * @param boolean $callback_only
     * @return array
     */
    public function trigger($event_name, $event = [], $callback_only = false)
    {       
        if (is_object($event) == false) {
            $event = new Event($event);   
        }
        if (($event instanceof EventInterface) == false) {
            throw new \Exception("Not valid event object.", 1);
        }

        $event->setName($event_name);          
        $result = [];

        if ($callback_only != true) {
            // get all subscribers for event
            $subscribers = Model::EventSubscribers()->getSubscribers($event_name,1);       
            $result = $this->executeEventHandlers($subscribers,$event);  
        }

        // run subscribed callback
        $callback_result = $this->runCallback($event_name,$event);

        return array_merge($result,$callback_result);
    }

    /**
     * Execute closure subscribers
     *
     * @param string $event_name
     * @param EventInterface $event
     * @return array
     */
    private function runCallback($event_name, $event)
    {
        if (isset($this->subscribers[$event_name]) == false) {
            return [];
        }
        $result = [];
        foreach ($this->subscribers[$event_name] as $callback) {
            if (Utils::isClosure($callback) == true) {
                $callback_result = $callback($event);
                array_push($result,$callback_result);
            }                  
        }
        return $result;
    }

    /**
     * Run event handlers
     *
     * @param array $event_subscribers
     * @param EventInterface $event
     * @return array 
     */
    private function executeEventHandlers(array $event_subscribers,Event $event)
    {       
        if (empty($event_subscribers) == true) {
            return [];
        }
        $result = [];
        foreach ($event_subscribers as $item) {
            $subscriber = Factory::createInstance($item['handler_class']);
            if (is_object($subscriber) == true && $subscriber instanceof EventSubscriberInterface) {
                $event_result = $subscriber->execute($event);
                array_push($result,$event_result);
            }
        }
        return $result;
    }
}
