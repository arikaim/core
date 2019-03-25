<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Queue\Providers;

use Arikaim\Core\Db\Model;
use Arikaim\Core\Utils\Factory;

use Arikaim\Core\Queue\Jobs\Job;
use Arikaim\Core\Interfaces\Queue\QueueInterface;
use Arikaim\Core\Interfaces\Queue\JobInterface;

class DbQueue implements QueueInterface
{
    public function __construct()
    {
    }

    public function add(JobInterface $job)
    {
        $model = Model::Jobs();

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
    
    public function remove(JobInterface $job)
    {
        $model = Model::Jobs();
        $id = $job->getId();
        $model = $model->where('uuid','=',$id);
        return $model->delete();
    }

    public function clear($completed = true, $extension_name = null)
    {
        $model = Model::Jobs();
        return $model->delete();
    }

    public function getNext()
    {
        $model = Model::Jobs();
        $model = $model->where('recuring_interval','=','');
        $model = $model->where('execution_time','=','')->orderBy('priority','desc')->first();
        if (is_object($model) == true) {
            return $this->createJobObject($model->toArray());
        }
        return null;
    }

    public function execute(JobInterface $job)
    {
        try {
            $job->execute();
            $this->updateExecutionTime($job);
        } catch (\Exception $e) {
            // handle job exception

            return false;
        }
        return true;
    }

    public function getJobs($recurring = false, $scheduled = false, $extenion_name = null)
    {
        $model = Model::Jobs();        
    }

    public function updateExecutionTime(JobInterface $job)
    {       
        $model = Model::Jobs()->where('uuid','=',$job->getId());
        if (is_object($model) == true) {           
            return $model->update(['executed' => time()]);
        }
        return false;
    }

    public function getExtensionJobs($extension_name)
    {
        $model = Model::Jobs();
        $model = $model->where('extension_name','=',$extension_name)->get();
        if (is_object($model) == true) {
            $jobs = $model->toArray();
            return $this->createJobsList($jobs);
        }
        return null;
    }

   







    public function getRecuringJobs($condition = null, $to_array = false)
    {
        $model = Model::Jobs();
        $model = Model::buildQuery($model,$condition);
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

    

    public function getScheduledJobs()
    {
        $model = Model::Jobs();
        $model = $model->where('schedule_time','>','0')->get();
        if (is_object($model) == true) {
            $jobs = $model->toArray();
            return $this->createJobsList($jobs);
        }
        return null;
    }

    /*
    
    public function getJobs($condition = null)
    {
        
        $model = Model::buildQuery($model,$condition);
        $model = $model->orderBy('priority','desc');
        $model = $model->get();
        if (is_object($model) == true) {
            return $model->toArray();
        }
        return [];
    }
    */

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
