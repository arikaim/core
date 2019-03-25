<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Interfaces\Queue;

use Arikaim\Core\Interfaces\Queue\JobInterface;

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
     * 
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
     * Return jobs count
     * @return number
     */
    public function getCount();

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
     * Undocumented function
     *
     * @param bool $recurring
     * @param boolean $scheduled
     * @param string $extenion_name
     * @return array
    */
    public function getJobs($recurring = false, $scheduled = false, $extenion_name = null);
}
