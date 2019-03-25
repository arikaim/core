<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/

interface ScheduledJobInterface
{   
    /**
     * Return schduled time (timestamp)
     *
     * @return number
     */
    public function getScheduleTime();
}
