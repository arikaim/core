<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\System\Console\Commands;

use Arikaim\Core\System\Console\ConsoleCommand;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;

use Arikaim\Core\Arikaim;

/**
 * Console shell
 */
class ShellCommand extends ConsoleCommand
{  
    /**
     * Config command
     * run php cli or php cli shell
     * @return void
     */
    protected function configure()
    {
        $this->setName('shell')->setDescription('Arikaim Cli');
        $this->setDefault(true);
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
        $app = $this->getApplication();
        $this->style->section($app->getName());   

        $helper = $this->getHelper('question');
        $question = new Question('arikaim > ');
      
        $app->setAutoExit(false);
      
        while(true) {
            $command = trim($helper->ask($input, $output, $question));
            if ($command == 'exit') { 
                $this->style->newLine();
                exit();
            }
            if (empty($command) == false) {
                $commandInput = new StringInput($command);
                $app->run($commandInput,$output);
            }
          
        }
        
        return true;
    }
}
