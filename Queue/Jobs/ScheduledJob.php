<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Queue\Jobs;

use Arikaim\Core\Queue\Jobs\Job;
use Arikaim\Core\Interfaces\Queue\ScheduledJobInterface;

abstract class ScheduledJob extends Job implements ScheduledJobInterface
{
    protected $schedule_time;

    abstract public function execute();
    
    public function runAt($date, $time = null)
    {
        if (empty($time) == true) {
            $time = "00:00:00";
        }
        $date = new \DateTime($date . "T" . $time);
        $this->setScheduleTime($date->getTimestamp());
    } 

    /**
     * ScheduledJobInterface implementation
     *
     * @return integer
     */
    public function getScheduleTime()
    {
        return $this->schedule_time;
    }

    /**
     * Set scheduled time (timestamp)
     *
     * @param ineteger $time
     * @return void
     */
    public function setScheduleTime($time)
    {
        $this->schedule_time = $time;
    }
}
