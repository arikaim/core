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

use Arikaim\Core\System\Console\ConsoleCommand;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;

use Arikaim\Core\Arikaim;

class ShellCommand extends ConsoleCommand
{  
    protected function configure()
    {
        $this->setName('shell')->setDescription('Arikaim Cli');
        $this->setDefault(true);
    }

    protected function executeCommand($input, $output)
    {
        $app = $this->getApplication();
        $this->style->section($app->getName());   

        $helper = $this->getHelper('question');
        $question = new Question('arikaim > ');
      
        $app->setAutoExit(false);
      
        while(true) {
            $command = $helper->ask($input, $output, $question);
            if ($command == 'exit') { 
                $this->style->newLine();
                exit();
            }
            $command_input = new StringInput($command);

            $app->run($command_input,$output);
        }
      
        return true;
    }
}
