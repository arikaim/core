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

interface QueueServiceInterface
{    
    public function addJob(JobInterface $job);
    public function removeJob(JobInterface $job);
    public function hasJob(JobInterface $job);
    public function removeAllJobs();
    public function getServiceDetails(); 
}
