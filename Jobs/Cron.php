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

use Arikaim\Core\Interfaces\Jobs\QueueServiceInterface;
use Arikaim\Core\Interfaces\Jobs\JobInterface;
use Arikaim\Core\Utils\Arrays;
use Arikaim\Core\Utils\TimeInterval;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Utils\DateTime;

class Cron implements QueueServiceInterface
{

    private $execute_script;

    public function __construct($execute_script = null)
    {
        $this->execute_script = $execute_script;
        if ($execute_script == null) {
            $this->execute_script = $this->getDefaultScript();
        } 
    }

    private function getDefaultScript()
    {
        $path = Arikaim::getArikaimPath();
        return "php $path/jobs.php >> /dev/null 2>&1";
    }

    public function isInstalled()
    {
        return true;
    }

    private function getRecurriungTime($interval)
    {
        if (empty($interval) == true) {
            $command_time = "* * * * *"; 
            return $command_time;
        }

        if (TimeInterval::isDurationInverval($interval) == false) {
            return $interval;
        }

        $time_interval = new TimeInterval($interval);
        $minutes = $time_interval->getMinutes();
        $hours = $time_interval->getHours();
        $months = $time_interval->getMonths();  
        $days = $time_interval->getDays();

        if ($minutes > 0) {
            return "*/$minutes * * * *";
        }
       
        if ($hours > 0) {
            return "* */$hours * * *";
        }
       
        if ($days > 0) {
            return "* * */$days * *";
        }     
        
        if ($months > 0) {
            return "* * * */$months *";
        }  

 

        return "$minutes $hours $days $months *";
    }

    public function getScheduledTime($time)
    {
        $date = new DateTime();
        $date->setTimestamp($time);
        $command_time = $date->getMinutes() . " ";
        $command_time .= $date->getHour() . " ";
        $command_time .= $date->getDay() . " ";
        $command_time .= $date->getMonth() . " ";
        $command_time .= "*";
      
        return $command_time;
    }

    public function getCommandTime(JobInterface $job)
    {
        if ($job->isRecuring() == true) {
            $interval = $job->getRecuringInterval();
            return $this->getRecurriungTime($interval);
        }

        if ($job->isScheduled() == true) {
            $time = $job->getScheduleTime();
            return $this->getScheduledTime($time);
        }
        return "* * * * *"; 
    }

    public function resolveCommand(JobInterface $job)
    {
        $command_time = $this->getCommandTime($job);
        return "$command_time " . $this->execute_script;
    }

    public function getCommands() {
        $output = shell_exec('crontab -l');
        return Arrays::toArray($output);
    }

    public function hasCommand($command)
    {
        $commands = $this->getCommands();
        if (in_array($command, $commands) == true) {
            return true;
        } 
        return false;
    }   

    public function removeAllJobs()
    {
        $output = shell_exec('crontab -r');
        return $output;
    }

    public function removeJob(JobInterface $job)
    {
        $command = $job->getJobCommand($job);
        return $this->removeCommand($command);
    }

    public function hasJob(JobInterface $job)
    {
        $command = $job->getJobCommand($job);
        return $this->hasCommand($command);
    }

    public function addJob(JobInterface $job)
    {
        $command = $this->resolveCommand($job);       
        $result = $this->addCommand($command);
        return $command;
    }

    public function addCommand($command)
    {
        if ($this->hasCommand($command) == true) {
            return false;
        }
        $commands = $this->getCommands();
        array_push($commands,$command);
        return $this->pushCommands($commands);
    }

    public function pushCommands(array $commands) 
    {
        $output = shell_exec('echo "'. Arrays::toString($commands).'" | crontab -');
        return $output; 
    }

    public function removeCommand($command) 
    {
        if ($this->hasCommand($command) == true) {
            $commands = $this->getCommands();
            unset($commands[array_search($command,$commands)]);
            return $this->pushCommands($commands);
        }
        return false;
    }

    public function getServiceDetails()
    {
        $details['name'] = "Cron";
        $details['installed'] =  $this->isInstalled();
        $details['jobs'] = $this->getCommands();
        $details['user'] = get_current_user();
        return $details;
    }
}
