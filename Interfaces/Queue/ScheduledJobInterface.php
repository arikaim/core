<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Interfaces\Queue;

use Arikaim\Core\Interfaces\Queue\JobInterface;

/**
 * Scheduled job interface
 */
interface ScheduledJobInterface extends JobInterface
{   
    /**
     * Return schduled time (timestamp)
     *
     * @return integer
     */
    public function getScheduleTime();

    /**
     * Set schedule time
     *
     * @param integer $timestamp
     * @return void
     */
    public function setScheduleTime($timestamp);
    
    /**
     * Return true if job is due
     *
     * @return boolean
     */
    public function isDue();
}
