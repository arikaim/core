<?php
/**
 *  Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
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
    protected $subscribed_events = [];

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
     * @param string $event_name
     * @param string|null $extension_name
     * @param integer $priority
     */
    public function __construct($event_name = null, $extension_name = null, $priority = 0)
    {
        if ($event_name != null) {
            $this->subscribe($event_name,$extension_name,$priority);
        }
    }
    
    /**
     * Subscribe to event.
     *
     * @param string $event_name
     * @param string|null $extension_name
     * @param integer $priority
     * @return void
     */
    public function subscribe($event_name, $extension_name = null, $priority = 0)
    {
        $event = [
            'event_name'     => $event_name,
            'priority'       => $priority,
            'extension_name' => $extension_name
        ];

        array_push($this->subscribed_events,$event); 
        $this->subscribed_events = array_unique($this->subscribed_events);
    }

    /**
     * Return subscribed events.
     *
     * @return array
     */
    public function getEvents() 
    {
        return $this->subscribed_events;
    }
}
