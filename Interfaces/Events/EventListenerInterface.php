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
 * Event listener interface
 */
interface EventListenerInterface
{    
    /**
     * Run listener code.
     *
     * @param EventInterface $event
     * @return bool
     */
    public function execute($event);
}