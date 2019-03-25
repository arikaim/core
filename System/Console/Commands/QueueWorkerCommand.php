<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System\Console\Commands;

use Arikaim\Core\System\Console\ConsoleCommand;
use Arikaim\Core\Queue\QueueWorker;

class QueueWorkerCommand extends ConsoleCommand
{  
    protected function configure()
    {
        $this->setName('queue:worker');
        $this->setDescription('Queue worker');
    }

    protected function executeCommand($input, $output)
    {
        $this->showTitle('Queue worker');
        $worker = new QueueWorker();
        $worker->run();
        
    }
}
