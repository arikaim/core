<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Interfaces\Queue;

use Arikaim\Core\Interfaces\Queue\JobInterface;

/**
 * Queue interface
 */
interface QueueInterface
{    
    /**
     * Add job to queue
     *
     * @param JobInterface $job
     * @return boolean
    */
    public function add(JobInterface $job);
    
    /**
     * Remove job from queue
     *
     * @param JobInterface $job
     * @return boolean
    */
    public function remove(JobInterface $job);
    
    /**
     * Execute job
     *
     * @param JobInterface $job
     * @return bool
    */
    public function execute(JobInterface $job);

    /**
     * Get next job
     * @return JobInterface
     */
    public function getNext();

    /**
     * Remove all jobs from queue
     * 
     * @param bool $completed - remove completed jobs only 
     * @param string extension_name - remove extension jobs only
     * @return void
    */
    public function clear($completed = true, $extension_name = null);

    /**
     * Get jobs list
     *
     * @param bool $recurring
     * @param boolean $scheduled
     * @param string $extenion_name
     * @return array
    */
    public function getJobs($recurring = false, $scheduled = false, $extenion_name = null);

    /**
     * Return true if job exist in queue
     *
     * @param JobInterface $job
     * @return boolean
     */
    public function hasJob(JobInterface $job);

    /**
     * Get recurring jobs
     *
     * @param string|null $extenion_name
     * @return array
     */
    public function getRecuringJobs($extenion_name = null);

    /**
    * Get scheduled jobs
    *
    * @param string|null $extenion_name
    * @return array
    */
    public function getScheduledJobs($extenion_name = null);

    /**
     * Get all jobs due
     * 
     * @return array
     */
    public function getJobsDue();
}
