<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\System\Console\Commands\Cache;

use Arikaim\Core\System\Console\ConsoleCommand;
use Arikaim\Core\Arikaim;

/**
 * Enable cache command
 * 
 */
class EnableCommand extends ConsoleCommand
{  
    /**
     * Command config
     * name cache:clear 
     * @return void
     */
    protected function configure()
    {
        $this->setName('cache:enable')->setDescription('Enable cache');
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
        $this->showTitle('Enable cache.');
        
        Arikaim::config()->setBooleanValue('settings/cache',true);
        $result = Arikaim::config()->save();

        Arikaim::cache()->clear();
        
        if ($result == true) {
            $this->showCompleted();
        } else {
            $this->showError("Can't enable cache!");
        }
    }
}
