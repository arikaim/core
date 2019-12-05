<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\Console\Commands\Queue;

use Arikaim\Core\Console\ConsoleCommand;
use Arikaim\Core\System\System;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Utils\Factory;

/**
 * Process cron jobs
 */
class CronCommand extends ConsoleCommand
{  
    /**
     * Configure command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('scheduler');
        $this->setDescription('Process cron jobs');
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
        // unlimited execution time
        System::setTimeLimit(0); 

        $this->showTitle('Scheduler');
        $jobs = Arikaim::queue()->getJobsDue();
        $this->style->writeLn('Jobs: ' . count($jobs));

        $executed = 0;
        foreach ($jobs as $item) {
            $job = Factory::createJobFromArray($item->toArray(),$item->handler_class);
            
            if ($job->isDue() == true) {       
                $executed++;      
                $name = (empty($job->getName()) == true) ? $job->getId() : $job->getName();

                $this->style->writeLn('ExecuteJob: ' . $name);
                Arikaim::queue()->executeJob($job);
            }
        }
        $this->style->writeLn('Executed jobs: ' . $executed);
    }
}
