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

use Arikaim\Core\System\System;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Queue\QueueManager;

class QueueWorker extends QueueManager
{
    /**
     * Run queue worker daemon
     *
     * @return void
     */
    public function run()
    {
        // set unlimited execution tile
        System::setTimeLimit(0); 
        // trigger event
        Arikaim::event()->trigger('core.jobs.queue.run',[]);

        while(true) {
            
            $job = $this->getNextJob();
            $result = $this->executeJob($job);

        }
    
    }
}
