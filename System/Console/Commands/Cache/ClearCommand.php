<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System\Console\Commands\Cache;

use Arikaim\Core\System\Console\ConsoleCommand;
use Arikaim\Core\Arikaim;

/**
 * Clear cache command
 * 
 */
class ClearCommand extends ConsoleCommand
{  
    /**
     * Command config
     * name cache:clear 
     * @return void
     */
    protected function configure()
    {
        $this->setName('cache:clear')->setDescription('Clear cache');
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
        $this->showTitle('Clear cache.');
      
        Arikaim::cache()->clear();

        $this->showCompleted();
    }
}