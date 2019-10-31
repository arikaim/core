<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Models;

use Illuminate\Database\Eloquent\Model;

use Arikaim\Core\Queue\Jobs\RecuringJob;

use Arikaim\Core\Traits\Db\DateCreated;
use Arikaim\Core\Traits\Db\Uuid;
use Arikaim\Core\Traits\Db\Find;
use Arikaim\Core\Traits\Db\Status;

/**
 * Jobs database model
 */
class Jobs extends Model  
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
    ];
    
    /**
     * Disable timestamps
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Return true if job is recurring 
     *
     * @return boolean
     */
    public function isRecurring()
    {
        return (empty($this->recuring_interval) == true) ? false : true;
    }

    /**
     * Return true if job is scheduled
     *
     * @return boolean
     */
    public function isScheduled()
    {
        return (empty($this->schedule_time) == true) ? false : true;
    }

    /**
     * Get attribute mutator for due_date
     *
     * @return integer
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
}
