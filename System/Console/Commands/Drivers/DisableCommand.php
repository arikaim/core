<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\System\Console\Commands\Drivers;

use Arikaim\Core\System\Console\ConsoleCommand;
use Arikaim\Core\System\Console\ConsoleHelper;
use Arikaim\Core\Arikaim;

/**
 * Disable driver command
 */
class DisableCommand extends ConsoleCommand
{  
    /**
     * Configure command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('drivers:disable')->setDescription('Disable driver');
        $this->addOptionalArgument('name','Driver full name');
    }

    /**
     * Execute command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function executeCommand($input, $output)
    {       
        $name = $input->getArgument('name');
        if (empty($name) == true) {
            $this->showError("Driver full name required!");
            return;
        }
    
        $result = Arikaim::driver()->disable($name);
        if ($result == false) {
            $this->showError("Driver $name not exists!");
            return;
        }

        $this->showCompleted();
    }
}
