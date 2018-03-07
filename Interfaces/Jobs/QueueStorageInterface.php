<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Interfaces\Jobs;

use Arikaim\Core\Interfaces\Jobs\JobInterface;

interface QueueStorageInterface
{    
    public function addJob(JobInterface $job);
    public function removeJob(JobInterface $job);
    public function updateExecutionTime(JobInterface $job);
    public function removeJobs($condition);
    public function getRecuringJobs(array $condition, $to_array = false);
    public function getScheduledJobs();
    public function getJobs($condition = null);
    public function getExtensionJobs($extension_name);
    public function getNextJob();
    public function clear();
}
