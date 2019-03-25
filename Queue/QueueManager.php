<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Queue;

use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Utils\Arrays;
use Arikaim\Core\Db\Model;

use Arikaim\Core\Interfaces\Queue\QueueInterface;
use Arikaim\Core\Interfaces\Queue\JobInterface;

use Arikaim\Core\Queue\Providers\DbQueue;
use Arikaim\Core\Arikaim;

class QueueManager 
{
    protected $queue;

    public function __construct(QueueInterface $queue = null)
    {       
        if ($queue == null) {
            // set default queue provider
            $this->queue = new DbQueue();
        }
    }

    public function setQueue(QueueInterface $queue)
    {
        $this->queue = $queue;
    }

    public function getQueue()
    {
        return $this->queue;
    }

    public function addJob($class_name, $extension_name = null)
    {
        $job = Factory::createJob($class_name,$extension_name);
        if ($job instanceof JobInterface) {
            return $this->queue->add($job);
        }
        return false;
    }

    public function removeJob(JobInterface $job)
    {
        return $this->queue->remove($job);
    }

    public function clear($completed = true, $extension_name = null)
    {
        $this->queue->clear($completed,$extension_name);
    }

    public function getNextJob()
    {
        return $this->queue->getNext();
    }

    public function executeJob($job)
    {
        // before run job event
        Arikaim::event()->trigger('core.jobs.before.execute',Arrays::convertToArray($job));
        try {
            $job->execute();
        } catch (\Exception $e) {
            // handle job exception

            return false;
        }
        // after run job event
        Arikaim::event()->trigger('core.jobs.after.execute',Arrays::convertToArray($job));
        return true;
    }
}
