<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System\Console\Commands;

use Symfony\Component\Console\Input\InputOption;
use Arikaim\Core\System\Console\ConsoleCommand;

class HelpCommand extends ConsoleCommand
{  
    protected function configure()
    {
        $this->setName('help')->setDescription('Arikaim Cli Help');
    }

    protected function executeCommand($input, $output)
    {
        $this->showTitle('Help');

        $command = $this->getApplication()->find('list');
        $command->run($input, $output);
    }
}
