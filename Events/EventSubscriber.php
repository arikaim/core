<?php
/**
 *  Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Events;

use Arikaim\Core\Interfaces\Events\EventSubscriberInterface;

/**
 * Base class for event subscribers.
*/
abstract class EventSubscriber implements EventSubscriberInterface
{
    /**
     * Events subscribed
     *
     * @var array
     */
    protected $subscribedEvents = [];

    /**
     * Subscriber code executed.
     *
     * @param EventInterface $event
     * @return void
     */
    abstract public function execute($event);

    /**
     * Constructor
     *
     * @param string $eventName
     * @param integer $priority
     */
    public function __construct($eventName = null, $priority = 0)
    {
        if ($eventName != null) {
            $this->subscribe($eventName,$priority);
        }
    }
    
    /**
     * Subscribe to event.
     *
     * @param string $eventName    
     * @param integer $priority
     * @return void
     */
    public function subscribe($eventName, $priority = 0)
    {
        $event = [
            'event_name'     => $eventName,
            'priority'       => $priority
        ];

        array_push($this->subscribedEvents,$event); 
        $this->subscribedEvents = array_unique($this->subscribedEvents);
    }

    /**
     * Return subscribed events.
     *
     * @return array
     */
    public function getSubscribedEvents() 
    {
        return $this->subscribedEvents;
    }
}
