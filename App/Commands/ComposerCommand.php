<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\App\Commands;

use Arikaim\Core\Console\ConsoleCommand;
use Symfony\Component\Console\Output\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Arikaim\Core\System\Composer;

/**
 * Composer command class
 */
class ComposerCommand extends ConsoleCommand
{  
    /**
     * Command config  
     * @return void
     */
    protected function configure()
    {
        $this->setName('composer')->setDescription('Composer');
        $this->addOptionalArgument('composer-command','Composer command');
        $this->addOptionalOption('option','Composer option');
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
        $this->showTitle('Composer');
        $command = $input->getArgument('composer-command');
        $option = $input->getOption('option');

        if (empty($option) == false) {
           $command .= ' -' . $option;
        }
    
        Composer::runCommand($command,false,true);  
    }
}
