<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\System\Console\Commands\Job;

use Arikaim\Core\System\Console\ConsoleCommand;
use Arikaim\Core\Arikaim;

/**
 * Run job command
 */
class RunJobCommand extends ConsoleCommand
{  
    /**
     * Configure command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('job:run')->setDescription('Run job.');
        $this->addOptionalArgument('name','Job Name');
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
        $this->showTitle('Run Job');
        $name = $input->getArgument('name');
        if (empty($name) == true) {
            $this->showError("Job name required!");
            return;
        }
        $this->style->writeLn('Name: ' . $name);

        $model = Arikaim::queue()->findJobNyName($name);
        if (is_object($model) == false) {
            $this->showError("Not valid job name!");
            return;
        }
        $job = Arikaim::queue()->create($name);
        $result = Arikaim::queue()->execute($job);
        
        if ($result === false) {
            $this->showError("Error execution job!");
        } else {
            $this->showCompleted();
        }      
    }    
}
