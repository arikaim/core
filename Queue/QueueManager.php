<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Queue;

use Arikaim\Core\Collection\Arrays;
use Arikaim\Core\Utils\Utils;

use Arikaim\Core\Interfaces\Queue\QueueInterface;
use Arikaim\Core\Interfaces\Queue\JobInterface;

use Arikaim\Core\Queue\Drivers\DbQueue;
use Arikaim\Core\Arikaim;

/**
 * Queue manager
 */
class QueueManager 
{
    /**
     * Queue driver
     *
     * @var QueueInterface
     */
    protected $driver;

    /**
     * Constructor
     *
     * @param QueueInterface $driver
     */
    public function __construct(QueueInterface $driver = null)
    {       
        if ($driver == null) {
            // set default queue provider
            $this->setDriver(new DbQueue());
        } else {
            $this->setDriver($driver);
        }
    }

    /**
     * Create cron scheduler
     *
     * @return object
     */
    public function createScheduler()
    {
        return new \Arikaim\Core\Queue\Cron();
    }

    /**
     * Create queue worker
     *
     * @return object
     */
    public function createWorker()
    {
        return new \Arikaim\Core\Queue\QueueWorker();
    }


    /**
     * Set queue provider
     *
     * @param QueueInterface $driver
     * @return void
     */
    public function setDriver(QueueInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Get queue provider
     *
     * @return QueueInterface
     */
    public function getQueue()
    {
        return $this->driver;
    }

    /**
     * Add job
     *
     * @param JobInterface $job
     * @param string|null $extension
     * @return bool
     */
    public function addJob(JobInterface $job, $extension = null)
    {       
        return $this->driver->add($job,$extension);      
    }

    /**
     * Delete job
     *
     * @param JobInterface $job
     * @return bool
     */
    public function removeJob(JobInterface $job)
    {
        return $this->driver->remove($job);
    }

    /**
     * Delete all jobs
     *
     * @param boolean $completed
     * @param  dtring|null $extension
     * @return void
     */
    public function clear($completed = true, $extension = null)
    {
        $this->driver->clear($completed,$extension);
    }

    /**
     * Get next job
     *
     * @return JobInterface|null
     */
    public function getNextJob()
    {
        return $this->driver->getNext();
    }

    /**
     * Run job
     *
     * @param JobInterface $job
     * @return void
     */
    public function executeJob(JobInterface $job)
    {
        // before run job event
        Arikaim::event()->dispatch('core.jobs.before.execute',Arrays::convertToArray($job));
        try {
            $result = $this->driver->execute($job);
        } catch (\Exception $e) {
            return false;
        }
        // after run job event
        Arikaim::event()->dispatch('core.jobs.after.execute',Arrays::convertToArray($job));
        return $result;
    }
    
    /**
     * Call other methods on driver
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return Utils::call($this->driver,$name,$arguments);
    }

   /**
     * Delete all extension jobs
     *
     * @param string $extension
     * @return boolean
     */
    public function deleteExtensionJobs($extension)
    { 
        return $this->driver->deleteExtensionJobs($extension);
    } 
}
