<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Queue\Drivers;

use Arikaim\Core\Db\Model;
use Arikaim\Core\Utils\DateTime;
use Arikaim\Core\Utils\Factory;

use Arikaim\Core\Interfaces\Queue\QueueInterface;
use Arikaim\Core\Interfaces\Queue\JobInterface;
use Arikaim\Core\Interfaces\Queue\RecuringJobInterface;
use Arikaim\Core\Interfaces\Queue\ScheduledJobInterface;

use Arikaim\Core\Traits\Db\Status;

/**
 * Queue storage provider save jobs to db
 */
class DbQueue implements QueueInterface
{
    /**
     * Add job
     *
     * @param JobInterface $job
     * @param string|null $extension
     * @return Model|false
     */
    public function add(JobInterface $job, $extension = null)
    {
        $model = Model::Jobs();
        $extension = ($extension == null) ? $job->getExtensionName() : $extension;
        $name = (empty($job->getName()) == true) ? null : $job->getName();

        if ($name != null) {
            $result = $model->findByColumn($name,'name');
            if (is_object($result) == true) {
                return false;
            }
        }

        $info = [
            'priority'              => $job->getPriority(),
            'name'                  => $name,
            'handler_class'         => get_class($job),         
            'extension_name'        => $extension,      
            'status'                => Status::$ACTIVE,
            'uuid'                  => $job->getId()
        ];

        if ($job instanceof RecuringJobInterface) {
            $info['recuring_interval'] = $job->getRecuringInterval();
            if ($this->hasJob($job) == true) {
                return false;
            }
        }

        if ($job instanceof ScheduledJobInterface) {
            $info['schedule_time'] = $job->getScheduleTime();
            if ($this->hasJob($job) == true) {
                return false;
            }
        }
        
        return $model->create($info);
    }

    /**
     * Get job by name 
     *
     * @param string $name
     * @param string|null $extension
     * @return Model|false
     */
    public function findJobNyName($name, $extension = null)
    {
        $model = Model::Jobs();
        $model = $model->where('name','=',$name);
        if (empty($extension) == false) {
            $model = $model->where('extension_name','=',$extension);
        }
        $model = $model->first();

        return (is_object($model) == true) ? $model : false;
    }

    /**
     * Find job by id or uuid
     *
     * @param string|integer $id
     * @return Model|false
     */
    public function findById($id)
    {
        return Model::Jobs()->findById($id);
    }

    /**
     * Return true if job exist in queue
     *
     * @param JobInterface $job
     * @return boolean
     */
    public function hasJob(JobInterface $job)
    {
        $model = Model::Jobs();
        $id = $job->getId();

        if ($id != null) {
            return (is_object($model->findById($id)) == false) ? false : true;
        }
        $model = $model->where('handler_class','=',get_class($job));

        if ($job instanceof RecuringJobInterface) {
            $model = $model->where('recuring_interval','=', $job->getRecuringInterval());
        }

        if ($job instanceof ScheduledJobInterface) {
            $model = $model->where('schedule_time','=',$job->getScheduleTime());
        }

        return is_object($model->first());
    }

    /**
     * Remove job
     *
     * @param JobInterface $job
     * @return bool
     */
    public function remove(JobInterface $job)
    {
        $id = $job->getId();
        if (empty($id) == true) {
            return false;
        }
        $model = Model::Jobs()->findById($id);

        return (is_object($model) == false) ? true : $model->delete();
    }

    /**
     * Create job obj from jobs queue
     *
     * @param string $name
     * @return JobInterface|false
     */
    public function create($name)
    {
        $model = $this->findJobNyName($name);
        if (is_object($model) == false) {
            return false;
        }

        return Factory::createJobFromArray($model->toArray(),$model->handler_class);
    }

    /**
     * Remove all jobs from queue
     *
     * @param integer|null $status
     * @param string|null $extension
     * @return bool
     */
    public function clear($status = null, $extension = null)
    {
        $model = Model::Jobs();
        if ($status != null) {
            $model = $model->where('status','=',$status);
        }
        if ($extension != null) {
            $model = $model->where('extension_name','=',$extension);
        }

        $result = $model->delete();

        return ($result == null) ? true : $result;
    }

    /**
     * Get next Job
     *
     * @return JobInterface|null
     */
    public function getNext()
    {
        $model = Model::Jobs();
        $model = $model->where('status','<>',$model->COMPLETED())
            ->whereNull('schedule_time')
            ->whereNull('recuring_interval')
            ->orderBy('priority','desc')->first();

        if (is_object($model) == false) {
            return null;
        }
        $job = Factory::createJob($model->handler_class ,$model->extension_name,$model->name,$model->priority);
        
        return (is_object($job) == true) ? $job : null;          
    }

    /**
     * Run job 
     *
     * @param JobInterface $job
     * @return bool
     */
    public function execute(JobInterface $job)
    {
        try {
            $job->execute();
            $this->updateJobExecutionStatus($job);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Get jobs
     *
     * @param string|null $extension
     * @param integer|null $status
     * @param string|null $scheduleTime
     * @param string|null $recuring
     * @return Model|null
     */
    public function getJobs($extension = null, $status = null, $scheduleTime = null, $recuring = null)
    {
        $model = Model::Jobs();  

        if ($extension != null) {
            $model = $model->where('extenion_name','=',$extension); 
        }
        if ($status != null) {
            $model = $model->where('status','=',$status); 
        }
        if ($scheduleTime != null) {
            $model = $model->where('schedule_time','=',$scheduleTime); 
        }
        if ($recuring != null) {
            $model = $model->where('recuring_interval','=',$recuring); 
        }
        $model = $model->orderBy('priority','desc')->get();

        return (is_object($model) == true) ? $model : null;
    }

    /**
     * Get not scheduled or recurrnign jobs
     *
     * @param string $extension
     * @param integer $status
     * @param boolean $queryOnly
     * @return Model|Bulder|null
     */
    public function getNotScheduledJobs($extension = null, $status = null, $queryOnly = true)
    {
        $model = Model::Jobs()->whereNull('recuring_interval')->whereNull('schedule_time'); 
       
        if ($extension != null) {
            $model = $model->where('extenion_name','=',$extension); 
        }
        if ($status != null) {
            $model = $model->where('status','=',$status); 
        }
        $model = $model->orderBy('priority','desc');

        if ($queryOnly == false) {
            $model = $model->get();
        }
     
        return (is_object($model) == true) ? $model : null;
    }

    /**
     * Get all jobs due
     * 
     * @return array
     */
    public function getJobsDue()
    {
        $model = Model::Jobs(); 
        $model = $model
            ->where('status','=',$model->ACTIVE())          
            ->where(function($query) {
                $query->where('recuring_interval','<>','')->orWhere('schedule_time','<',DateTime::toTimestamp());
            })->orderBy('priority','desc')->get();
            
        return $model;
    }

    /**
     * Get recurring jobs
     *
     * @param string|null $extension
     * @return array
     */
    public function getRecuringJobs($extension = null)
    {   
        $model = Model::Jobs()->whereNotNull('recuring_interval');

        if ($extension != null) {
            $model = $model->where('extension_name','=',$extension); 
        }
        $model = $model->get();

        return (is_object($model) == true) ? $model : [];
    }

    /**
     * Get scheduled jobs
     *
     * @param string|null $extension
     * @return array
     */
    public function getScheduledJobs($extension = null)
    {
        $model = Model::Jobs()->whereNotNull('schedule_time');

        if ($extension != null) {
            $model = $model->where('extension_name','=',$extension); 
        }
        $model = $model->get();

        return (is_object($model) == true) ? $model : [];
    }

    /**
     * Update execution status
     *
     * @param JobInterface $job
     * @return bool
     */
    public function updateJobExecutionStatus(JobInterface $job)
    {       
        $id = (empty($job->getId()) == true) ? $job->uuid : $job->getId();
        $model = Model::Jobs()->findByIdQuery($id);
    
        if (is_object($model->first()) == false) {
            return false;
        } 
        if ($job instanceof RecuringJobInterface) {
            $info = ['date_executed' => DateTime::toTimestamp()];
        }
        if ($job instanceof ScheduledJobInterface) {
            $info = ['date_executed' => DateTime::toTimestamp(),'status' => $model->first()->COMPLETED()];
        }
        // increment execution counter
        $model->increment('executed');
        $result = $model->update($info);     

        return ($result == null) ? true : $result;
    }

    /**
     * Delete all extension jobs
     *
     * @param string $extension
     * @return boolean
     */
    public function deleteExtensionJobs($extension)
    {
        $model = Model::Jobs()->where('extension_name','=',$extension);
        
        return $model->delete();
    } 
}
