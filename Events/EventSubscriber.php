<?php
/**
 *  Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Events;

use Arikaim\Core\Arikaim;
use Arikaim\Core\Utils\Number;
use Arikaim\Core\Interfaces\EventSubscriberInterface;

abstract class EventSubscriber implements EventSubscriberInterface
{
    protected $subscribed_events = [];

    public function __construct() 
    {
    }

    public function subscribe($event_name, $extension_name = "", $priority = 0)
    {   
        $event['event_name'] = $event_name;
        $event['priority'] = Number::getNumericValue($priority);
        array_push($this->subscribed_events,$event); 
        $this->subscribed_events = array_unique($this->subscribed_events);
    }

    public function execute($event)
    {
    }

    public function getEvents() 
    {
        return $this->subscribed_events;
    }
}
