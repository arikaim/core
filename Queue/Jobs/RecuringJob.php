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
use Arikaim\Core\Utils\TimeInterval;
use Arikaim\Core\Interfaces\Queue\RecuringJobInterface;

abstract class RecuringJob extends Job implements RecuringJobInterface
{
    protected $interval;
    protected $recuring_interval;

    public function __construct($extension_name, $name = "", $priority = 0)
    {
        parent::__construct($extension_name,$name,$priority);
        $this->interval = new TimeInterval();
    }

    abstract public function execute();

    /**
     * RecuringJobInterface implementation function
     *
     * @return string
     */
    public function getRecuringInterval()
    {
        return $this->recuring_interval;
    }

    public function runEveryMinute() 
    {
        $this->runEveryMinutes(1);
    }

    public function runEveryMinutes($minutes = 1) 
    {
        $this->interval->setMinutes($minutes);
        $this->recuring_interval = $this->time_interval->getInterval();
    }
    
    public function runEveryHour() 
    {
        $this->runEveryHours(1);
    }

    public function runEveryHours($hours = 0) 
    {
        $this->interval->setHours($hours);
        $this->recuring_interval = $this->time_interval->getInterval();
    }

    public function runEveryDay() 
    {
        $this->runEveryDays(1);
    }

    public function runEveryDays($days = 0) 
    {
        $this->interval->setDays($days);
        $this->recuring_interval = $this->time_interval->getInterval();
    }
    
    public function runEveryMonth() 
    {
        $this->runEveryMonths(1);
    }

    public function runEveryMonths($months = 0) 
    {
        $this->interval->setMonths($months);
        $this->recuring_interval = $this->time_interval->getInterval();
    }

    public function custom($text)
    {
        $this->recuring_interval = $text;
    }
}
