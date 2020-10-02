<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\App\Commands\Cache;

use Arikaim\Core\Console\ConsoleCommand;
use Arikaim\Core\Console\ConsoleHelper;
use Arikaim\Core\Arikaim;

/**
 * Show cache driver command
 * 
 */
class DriverCommand extends ConsoleCommand
{  
    /**
     * Command config
     * name cache:clear 
     * @return void
     */
    protected function configure()
    {
        $this->setName('cache:driver')->setDescription('Cache driver info');
    }

    /**
     * Command code
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     */
    protected function executeCommand($input, $output)
    {
        $this->showTitle('Cache driver'); 
        $driver = Arikaim::cache()->getDriver();

        $this->style->write(ConsoleHelper::getLabelText('Class ','cyan')); 
        $this->style->write(get_class($driver));
        $this->style->newLine();
        $this->style->write(ConsoleHelper::getLabelText('Name ','cyan')); 
        $this->style->write(Arikaim::cache()->getDriverName());
       
        $this->style->newLine();
        $this->showCompleted();
        $this->style->newLine();
    }
}
