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

use Arikaim\Core\Interfaces\Jobs\QueueStorageInterface;
use Arikaim\Core\Interfaces\Jobs\QueueServiceInterface;
use Arikaim\Core\Interfaces\Jobs\JobInterface;
use Arikaim\Core\System\System;
use Arikaim\Core\Jobs\Cron;
use Arikaim\Core\Jobs\DbJobsQueue;
use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Utils\Arrays;
use Arikaim\Core\Db\Model;

class JobsQueueManager 
{
    private $queue_storage;
    private $queue_service;
    private $cron;
    private $run_counter;

    public function __construct(QueueStorageInterface $queue_storage = null,QueueServiceInterface $queue_service = null)
    {
        $this->queue_storage = $queue_storage;
        if ($this->queue_storage == null) {
            $this->queue_storage = new DbJobsQueue();
        }

        $this->queue_service = $queue_service;
        if ($this->queue_service == null) {
            $this->queue_service = new Cron();
        }
    }

    public function createJob($class_name, $extension_name = null, $args = null)
    {
        return Factory::createJob($class_name,$extension_name,$args);
    }

    public function setQueueService(QueueServiceInterface $queue_service)
    {
        $this->queue_service = $queue_service;
    }

    public function setStorage(QueueStorageInterface $queue_storage)
    {
        $this->queue_storage = $queue_storage;
    }

    public function addJob(JobInterface $job)
    {
        $command = $this->getQueueService()->addJob($job);
        if ($command != null) {
            $job->setJobCommand($command);
            $result = $this->getStorage()->addJob($job);
            return $result;
        }
        return false;
    }

    public function removeJob(JobInterface $job)
    {
        $result = $this->getStorage()->removeJob($job);
        if ($result == true) {
            $result = $this->getQueueService()->removeJob($job);
            return $result;
        }
        return false;
    }

    public function removeJobs($condition)
    {
        $this->getStorage()->removeJobs($condition);
    }

    public function clear()
    {
        $this->getStorage()->clear();
    }

    public function getStorage()
    {
        return $this->queue_storage;
    }

    public function getQueueService()
    {
        return $this->queue_service;
    }

    public function run()
    {
        System::setTimeLimit(3600); // 1h time limit

        $this->run_counter = 0;
        System::writeLine("Arikaim Jobs queue");
        
        $this->runRecuringJobs();
        $this->runScheduledJobs(); 

        $job = $this->getStorage()->getNextJob();
        if ($job != null) {
            $this->executeJob($job);
        }
        // trigger event
        Arikaim::event()->trigger('core.jobs.queue.run',['jobs_executed' => $this->run_counter]);
    }

    public function runRecuringJobs($condition = null)
    {
        $jobs = $this->getStorage()->getRecuringJobs($condition);
        if (is_array($jobs) == false) {
            return false;
        }
        foreach ($jobs as $job) {
            $this->executeJob($job);
        }
        return true;
    }

    public function executeJob($job)
    {
        // before run job event
        Arikaim::event()->trigger('core.jobs.before.execute',Arrays::convertToArray($job));
        $this->run_counter++;
        $job->execute();
        // after run job event
        Arikaim::event()->trigger('core.jobs.after.execute',Arrays::convertToArray($job));
        if ($job->isRecuring() == false) {
            $this->removeJob($job);
        } else {
            $this->getStorage()->updateExecutionTime($job);
        }
    }

    public function runScheduledJobs()
    {
        $jobs = $this->getStorage()->getScheduledJobs();
        if (is_array($jobs) == false) {
            return false;
        }
        foreach ($jobs as $job) {
            $this->executeJob($job);
            Arikaim::logger()->info('Execute Scheduled job');
        }
        return true;
    }

    public function getServiceDetails()
    {
        return $this->getQueueService()->getServiceDetails();
    }

    public function deleteExtensionJobs($extension_name)
    {
        $jobs = $this->getStorage()->getExtensionJobs($extension_name);
        // delete jobs from db
        $this->getStorage()->removeJobs(Model::createCondition('extension_name','=',$extension_name));
        if (is_array($jobs) == false) {
            return true;
        }

        foreach ($jobs as $job) {
            $command = $job->getJobCommand();
            $this->getQueueService()->removeCommand($command);
        }
    }

    public function update()
    {
        $jobs = $this->getStorage()->getJobs();
        foreach ($jobs as $item) {
            $job = Factory::createInstance($item['handler_class']);
            $job->init($item);
            $this->getQueueService()->addJob($job);
        } 
    }

    public function getJobs($condition = null)
    {
        $jobs = $this->getStorage()->getJobs($condition);
        if (is_array($jobs) == false) {
            return [];
        }
        foreach ($jobs as $index => $item) {
            $job = Factory::createInstance($item['handler_class']);
            $job->init($item);
            if ($this->getQueueService()->hasJob($job) == true) {
                $jobs[$index]['status'] = 1;
            } else {
                $jobs[$index]['status'] = 0;
            }
        }
        return $jobs;
    }
}
