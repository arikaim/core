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
 * Disable cache command
 * 
 */
class DisableCommand extends ConsoleCommand
{  
    /**
     * Command config
     * name cache:clear 
     * @return void
     */
    protected function configure()
    {
        $this->setName('cache:disable')->setDescription('Disable cache');
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
        $this->showTitle('Disable cache.');
        
        Arikaim::cache()->clear();
        
        Arikaim::config()->setBooleanValue('settings/cache',false);
        $result = Arikaim::config()->save();
        if ($result == true) {
            $this->showCompleted();
        } else {
            $this->showError("Can't disable cache!");
        }
    }
}
