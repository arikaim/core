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

use Arikaim\Core\Db\Model;
use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Jobs\Job;
use Arikaim\Core\Interfaces\Jobs\QueueStorageInterface;
use Arikaim\Core\Interfaces\Jobs\JobInterface;

class DbJobsQueue implements QueueStorageInterface
{
    public function __construct()
    {
    }

    public function addJob(JobInterface $job)
    {
        $model = Model::JobsQueue();

        $job_info['priority'] = $job->getPriority();
        $job_info['name'] = $job->getName();
        $job_info['handler_class'] = get_class($job);
        $job_info['recuring_interval'] = $job->getRecuringInterval();
        $job_info['execution_time'] = $job->getExecutionTime();
        $job_info['extension_name'] = $job->getExtensionName();
        $job_info['job_command'] = $job->getJobCommand();
        $job_info['status'] = 1;
        $job_info['uuid'] = $job->getId();
        $job_info['schedule_time'] = $job->getScheduleTime();

        $model->fill($job_info);
        return $model->save();
    }

    public function updateExecutionTime(JobInterface $job)
    {
        $uuid = $job->getId();
        $model = Model::JobsQueue()->where('uuid','=',$uuid);
        if (is_object($model) == true) {
            $time = time(); 
            return $model->update(['executed' => $time]);
        }
        return false;
    }

    public function getExtensionJobs($extension_name)
    {
        $model = Model::JobsQueue();
        $model = $model->where('extension_name','=',$extension_name)->get();
        if (is_object($model) == true) {
            $jobs = $model->toArray();
            return $this->createJobsList($jobs);
        }
        return null;
    }

    public function removeJob(JobInterface $job)
    {
        $model = Model::JobsQueue();
        $id = $job->getId();
        $model = $model->where('uuid','=',$id);
        return $model->delete();
    }

    public function removeJobs($condition)
    {
        $model = Model::JobsQueue();
        $model = Model::applyCondition($model,$condition);
        return $model->delete();
    }

    public function clear()
    {
        $model = Model::JobsQueue();
        return $model->delete();
    }

    public function getRecuringJobs($condition = null, $to_array = false)
    {
        $model = Model::JobsQueue();
        $model = Model::applyCondition($model,$condition);
        $model = $model->where('recuring_interval','<>','')->get();
        if (is_object($model) == true) {
            $jobs = $model->toArray();
            if ($to_array == true) {
                return $jobs;
            }
            return $this->createJobsList($jobs);
        }
        return null;
    }

    public function getNextJob()
    {
        $model = Model::JobsQueue();
        $model = $model->where('recuring_interval','=','');
        $model = $model->where('execution_time','=','')->orderBy('priority','desc')->first();
        if (is_object($model) == true) {
            return $this->createJobObject($model->toArray());
        }
        return null;
    }

    public function getScheduledJobs()
    {
        $model = Model::JobsQueue();
        $model = $model->where('schedule_time','>','0')->get();
        if (is_object($model) == true) {
            $jobs = $model->toArray();
            return $this->createJobsList($jobs);
        }
        return null;
    }

    public function getJobs($condition = null)
    {
        $model = Model::JobsQueue();        
        $model = Model::applyCondition($model,$condition);
        $model = $model->orderBy('priority','desc');
        $model = $model->get();
        if (is_object($model) == true) {
            return $model->toArray();
        }
        return [];
    }
    
    private function createJobsList(array $jobs)
    {
        $result = [];
        foreach ($jobs as $job) {
            $job = $this->createJobObject($job);
            array_push($result,$job);
        }
        return $result;
    }

    private function createJobObject(array $data)
    {
        $job = Factory::createInstance($data['handler_class']);
        $job->init($data);
        return $job;
    }
}
