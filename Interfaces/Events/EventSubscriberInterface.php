<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Interfaces\Events;

/**
 * Event subscriber interface
 */
interface EventSubscriberInterface
{    
    /**
     * Subscriber code.
     *
     * @param EventInterface $event
     * @return bool
     */
    public function execute($event);

    /**
     * Return subscribed events.
     *
     * @return array
     */
    public function getSubscribedEvents();
}
