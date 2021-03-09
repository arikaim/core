<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Models;

use Illuminate\Database\Eloquent\Model;

use Arikaim\Core\Utils\DateTime;
use Arikaim\Core\Interfaces\Job\QueueStorageInterface;
use Arikaim\Core\Queue\Jobs\RecuringJob;
use Arikaim\Core\Interfaces\Job\JobInterface;
use Arikaim\Core\Interfaces\Job\RecuringJobInterface;

use Arikaim\Core\Db\Traits\DateCreated;
use Arikaim\Core\Db\Traits\Uuid;
use Arikaim\Core\Db\Traits\Find;
use Arikaim\Core\Db\Traits\Status;

/**
 * Jobs database model
 */
class Jobs extends Model implements QueueStorageInterface
{
    use Uuid,
        Find,
        Status,
        DateCreated;
 
    /**
     * Fillable attributes
     *
     * @var array
    */
    protected $fillable = [
        'name',
        'priority',
        'recuring_interval',
        'handler_class',      
        'status',
        'extension_name',
        'schedule_time',
        'date_created',
        'date_executed',
        'executed',
        'config',
        'queue'
    ];
    
    /**
     * Db table name
     *
     * @var string
     */
    protected $table = 'jobs';

    /**
     * Disable timestamps
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Append custom attributes
     *
     * @var array
     */
    public $appends = [
        'due_date'
    ];

    /**
     * Mutator (get) for config attribute.
     *
     * @return array
     */
    public function getConfigAttribute()
    {
        return (empty($this->attributes['config']) == true) ? [] : \json_decode($this->attributes['config'],true);
    }
    
    /**
     * Get attribute mutator for due_date
     *
     * @return integer|null
     */
    public function getDueDateAttribute()
    {
        if ($this->isRecurring() == true) {
            return (empty($this->recuring_interval) == false) ? RecuringJob::getNextRunDate($this->recuring_interval) : null;
        }
        if ($this->isScheduled() == true) {
            return $this->schedule_time;
        }
    }

    /**
     * Return true if job is recurring 
     *
     * @return boolean
     */
    public function isRecurring(): bool
    {
        return (empty($this->recuring_interval) == false);
    }

    /**
     * Return true if job is scheduled
     *
     * @return boolean
     */
    public function isScheduled(): bool
    {
        return (empty($this->schedule_time) == false);
    }

    /**
     * Find job and return job id
     *
     * @param array $filter
     * @return string|false
     */
    public function getJobId(array $filter = [])
    {
        $model = $this;
        foreach ($filter as $key => $value) {
            $model = ($value == '*') ? $model->whereNotNull($key) : $model->where($key,'=',$value);
        }
        $model = $model->first();

        return (\is_object($model) == true) ? $model->uuid : false;
    }

    /**
     * Add job
     *
     * @param array $data
     * @return boolean
     */
    public function addJob(array $data): bool
    {
        $model = $this->findByColumn($data['name'],'name');

        if (\is_object($model) == true) {
            return false;
        }
        $model = $this->create($data);

        return \is_object($model);
    }
    
    /**
     * Delete job
     *
     * @param string|integer $id
     * @return boolean
     */
    public function deleteJob($id): bool
    {
        $model = $this->findById($id);

        return (\is_object($model) == true) ? (bool)$model->delete() : false;
    }

    /**
     * Delete jobs
     *
     * @param array $filter
     * @return boolean
     */
    public function deleteJobs(array $filter = []): bool
    {
        $model = $this;
        foreach ($filter as $key => $value) {
            $model = ($value == '*') ? $model->whereNotNull($key) : $model->where($key,'=',$value);
        }

        return (bool)$model->delete();
    }

    /**
     * Get job
     *
     * @param string|integer $id Job id, uiid or name
     * @return array|null
     */
    public function getJob($id): ?array
    {
        $model = $this->findById($id);

        if (\is_object($model) == false) {
            $model = $this->findByColumn($id,'name');
        }
        
        return (\is_object($model) == true) ? $model->toArray() : null;
    }

    /**
     * Save job config
     *
     * @param string|int $id
     * @param array $config
     * @return boolean
     */
    public function saveJobConfig($id, array $config): bool
    {
        $model = $this->findById($id);
        if (empty($model) == true) {
            $model = $this->findByColumn($id,'name');
        }
        if (empty($model) == true) {
            return false;
        }

        return (bool)$model->update([
            'config' => \json_encode($config)
        ]);
    }

    /**
     * Return true if job exists
     *
     * @param string|integer $id Job id, uiid
     * @return boolean
     */
    public function hasJob($id): bool
    {
        $model = $this->findById($id);
        if (\is_object($model) == false) {
            $model = $this->findByColumn($id,'name');
        }
        
        return \is_object($model);
    }

    /**
     * Get jobs
     *
     * @param array $filter   
     * @return array
     */
    public function getJobs(array $filter = []): ?array
    {  
        $model = $this;
        foreach ($filter as $key => $value) {
            $model = ($value == '*') ? $model->whereNotNull($key) : $model->where($key,'=',$value);
        }
        $model = $model->get();

        return (\is_object($model) == true) ? $model->toArray() : null;
    }

    /**
     * Update execution status
     *
     * @param string|integer $id
     * @param integer $status
     * @return boolean
     */
    public function setJobStatus($id, int $status): bool
    {
        $model = $this->findById($id);
        if (\is_object($model) == false) {
            return false;
        } 

        return (bool)$model->update(['status' => $status]);
    }

    /**
     * Update execution status
     *
     * @param JobInterface $job
     * @return bool
     */
    public function updateExecutionStatus(JobInterface $job): bool
    {       
        $id = (empty($job->getId()) == true) ? $job->getName() : $job->getId();
        $model = $this->findByIdQuery($id);
    
        if (\is_object($model->first()) == false) {
            return false;
        } 
        if ($job->getStatus() != JobInterface::STATUS_EXECUTED) {
            return false;
        }
        $status = ($job instanceof RecuringJobInterface) ? JobInterface::STATUS_EXECUTED : JobInterface::STATUS_COMPLETED;
        $info = [
            'date_executed' => DateTime::toTimestamp(),
            'status'        => $status
        ];

        // increment execution counter
        $model->increment('executed');

        return (bool)$model->update($info);            
    }

    /**
     * Get next Job
     *
     * @return array|null
     */
    public function getNext(): ?array
    {       
        $query = $this->getJobsDueQuery();       
        $model = $query->orderBy('priority','desc')->first();

        return (\is_object($model) == true) ? $model->toArray() : null;           
    }

    /**
     * Get all jobs due
     * 
     * @return array|null
     */
    public function getJobsDue(): ?array
    {
        $query = $this->getJobsDueQuery();       
        $model = $query->orderBy('priority','desc')->get();

        return (\is_object($model) == true) ? $model->toArray() : null;
    }

    /**
     * Get jobs due query
     *
     * @return Builder
     */
    public function getJobsDueQuery()
    {
        return $this
            ->where('status','<>',JobInterface::STATUS_COMPLETED)        
            ->where('status','<>',JobInterface::STATUS_ERROR)      
            ->where('status','<>',JobInterface::STATUS_SUSPENDED)          
            ->where(function($query) {
                //$query->where('recuring_interval','<>','')->orWhere('schedule_time','<',DateTime::toTimestamp());
                $query->whereNull('schedule_time')->orWhere('schedule_time','<',DateTime::toTimestamp());
            }
        );    
    }
}
