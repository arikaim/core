<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\System\Console\Commands;

use Arikaim\Core\System\Console\ConsoleCommand;

/**
 * Help command class
 */
class HelpCommand extends ConsoleCommand
{  
    /**
     * Command config
     * name: help
     * @return void
     */
    protected function configure()
    {
        $this->setName('help')->setDescription('Arikaim Cli Help');
    }

    /**
     * Command code
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function executeCommand($input, $output)
    {
        $this->showTitle('Help');

        $command = $this->getApplication()->find('list');
        $command->run($input, $output);
    }
}
