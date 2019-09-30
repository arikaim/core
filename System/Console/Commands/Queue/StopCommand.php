<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System\Console\Commands\Queue;

use Arikaim\Core\System\Console\ConsoleCommand;
use Arikaim\Core\Queue\QueueWorker;
use Arikaim\Core\System\System;
use Arikaim\Core\System\Error\Errors;
use Arikaim\Core\Arikaim;

/**
 * Queueu worker stop
 */
class StopCommand extends ConsoleCommand
{  
    /**
     * Configure command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('queue:stop');
        $this->setDescription('Queue worker stop');
    }

    /**
     * Run command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function executeCommand($input, $output)
    {
        $this->showTitle('Stop queue worker');

        $worker = new QueueWorker();
        $pid = $worker->getPid();
        
        $this->style->writeLn('Worker pid: ' . $pid);

        $result = ($pid != null) ? posix_kill($pid,15) : false;

        if ($result == true) {
            Arikaim::options()->set('queue.worker.pid',null);
            Arikaim::options()->set('queue.worker.command',null);  
            $this->style->writeLn('Done ' . $result);
        } else {
            $error = Errors::getPosixError();
            $this->style->writeLn('Error: ' . $error);
        }
    }
}
