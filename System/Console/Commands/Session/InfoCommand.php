<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\System\Console\Commands\Session;

use Arikaim\Core\System\Console\ConsoleCommand;
use Arikaim\Core\Arikaim;

/**
 * Session info
 * 
 */
class InfoCommand extends ConsoleCommand
{  
    /**
     * Command config
     * name cache:clear 
     * @return void
     */
    protected function configure()
    {
        $this->setName('session:info')->setDescription('Session info');
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
        $this->showTitle('Session info.');
        
        $label = (Arikaim::session()->isUseCookies() == true) ? 'true' : 'false';

        $this->style->writeLn('Id: ' . Arikaim::session()->getId());
        $this->style->writeLn('Use cookies: ' . $label);
        $this->style->writeLn('Save Path: ' . ini_get( 'session.save_path'));      
        $this->style->writeLn('Lifetime: ' . Arikaim::session()->getLifetime());

        $this->showCompleted();
    }
}
