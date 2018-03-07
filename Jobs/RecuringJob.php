<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Jobs;

use Arikaim\Core\Jobs\Job;
use Arikaim\Core\Utils\TimeInterval;

class RecuringJob extends Job
{

    private $time_interval;

    public function execute()
    {
    }

    public function __construct($name = "", $priority = 0, $uuid = null)
    {
        parent::__construct($name,$priority,$uuid); //P0Y0M0D0H0M
        $this->time_interval = new TimeInterval();
    }

    public function runEveryMinute() 
    {
        $this->runEveryMinutes(1);
    }

    public function runEveryMinutes($minutes = 1) 
    {
        $this->time_interval->setMinutes($minutes);
        $this->recuring_interval = $this->time_interval->getInterval();
    }
    
    public function runEveryHour() 
    {
        $this->runEveryHours(1);
    }

    public function runEveryHours($hours = 0) 
    {
        $this->time_interval->setHours($hours);
        $this->recuring_interval = $this->time_interval->getInterval();
    }

    public function runEveryDay() 
    {
        $this->runEveryDays(1);
    }

    public function runEveryDays($days = 0) 
    {
        $this->time_interval->setDays($days);
        $this->recuring_interval = $this->time_interval->getInterval();
    }
    
    public function runEveryMonth() 
    {
        $this->runEveryMonths(1);
    }

    public function runEveryMonths($months = 0) 
    {
        $this->time_interval->setMonths($months);
        $this->recuring_interval = $this->time_interval->getInterval();
    }

    public function custom($text)
    {
        $this->recuring_interval = $text;
    }
}
