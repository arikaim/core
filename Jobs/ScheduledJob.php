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

class ScheduledJob extends Job
{
    public function execute()
    {
    }

    public function runAt($date, $time = null)
    {
        if ($time == null) {
            $time = "00:00:00";
        }
        $date = new \DateTime($date . "T" . $time);
        $this->setScheduleTime($date->getTimestamp());
    } 
}
