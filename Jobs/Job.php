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

use Arikaim\Core\Interfaces\Jobs\JobInterface;
use Arikaim\Core\Utils\Utils;

abstract class Job implements JobInterface
{
    protected $uuid;
    protected $name;
    protected $priority;
    protected $recuring_interval;
    protected $execution_time;
    protected $status;
    protected $extension_name;
    protected $job_command;
    protected $schedule_time;

    abstract public function execute();

    public function __construct($name = "", $priority = 0, $uuid = null)
    {
        $this->uuid = $uuid;
        $this->init();
        $this->setName($name);
        $this->setPriority($priority);
    }

    public function init(array $data = null)
    {
        if (is_array($data) == false) {
            if ($this->uuid == null) {
                $this->uuid = Utils::getUUID();
            }
            $this->status = 1;
            $this->schedule_time = 0;
            $this->name = "";
            $this->priority = 0;
            $this->recuring_interval = "";
            $this->execution_time = "";
            $this->extension_name = "";
            $this->job_command = "";
            return true;
        }
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function setJobCommand($command)
    {
        $this->job_command = $command;
    }

    public function getId()
    {
        return $this->uuid;
    }

    public function getExtensionName()
    {
        return $this->extension_name;
    }

    public function getJobCommand()
    {
        return $this->job_command;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function getRecuringInterval()
    {
        return $this->recuring_interval;
    }

    public function getExecutionTime()
    {
        return $this->execution_time;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    public function setRecuringInterval($interval)
    {
        $this->recuring_interval = $interval;
    }

    public function setExtensionName($name)
    {
        return $this->extension_name = $name;
    }

    public function setExecutionTime($date_time)
    {
        $this->execution_time = $date_time;
    }

    public function isDisabled()
    {
        if ($this->status == 0) {
            return true;
        }
        return false;
    }

    public function isRecuring()
    {
        if (empty($this->recuring_interval) == true) {
            return false;
        }
        return true;
    }

    public function isScheduled()
    {
        if ($this->schedule_time > 0) {
            return true;
        }
        return false;
    }

    public function setScheduleTime($time)
    {
        $this->schedule_time = $time;
    }

    public function getScheduleTime()
    {
        return $this->schedule_time;
    }
}
