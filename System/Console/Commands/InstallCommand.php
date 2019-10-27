<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System\Console\Commands;

use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;

use Arikaim\Core\System\Console\ConsoleCommand;
use Arikaim\Core\System\ComposerApplication;
use Arikaim\Core\System\Console\ConsoleHelper;
use Arikaim\Core\System\System;
use Arikaim\Core\System\Install;

/**
 * Install command class //TODO
 */
class InstallCommand extends ConsoleCommand
{  
    /**
     * Command config
     * name: install
     * @return void
     */
    protected function configure()
    {
        $this->setName('install')->setDescription('Arikaim Install');
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
        $this->showTitle('Arikaim CMS installation');
      
        if (Install::isInstalled() == true) {
            $this->style->text('already installed.');
            $this->style->newLine();
         //  return true;
        }
      

        //Requirements
        $this->style->text('Requirements');
        $this->style->newLine();
        $requirements = System::checkSystemRequirements();

        // status - 0 red , 1 - ok,  2 - oarange
        foreach ($requirements['items'] as $item) {
            if ($item['status'] == 1) {
                $label = ConsoleHelper::getLabelText("\t" . $item['message'],'green');
                $this->style->writeLn($label);
            }
        }
        if (count($requirements['errors']) > 0) {
            $this->style->newLine();
            $this->style->writeLn(ConsoleHelper::getDescriptionText('Errors'));
            foreach ($requirements['errors'] as $error) {
                $label = ConsoleHelper::getLabelText($error,'red');
                $this->style->writeLn($label);
            }
        }
        $this->style->newLine();
        $this->style->text(ConsoleHelper::getDescriptionText('Database'));
         
        $helper = $this->getHelper('question');
        $validator = function($value) {                
            if (empty(trim($value)) == true) {
                throw new \Exception('Cannot be empty');              
                return null;
            }
            return $value;
        };
        $question = new Question("\t Enter database Name: ",null);    
        $question->setValidator($validator);      
        $databaseName = $helper->ask($input, $output, $question);
        
        $question = new Question("\t Enter database Username: ");
        $question->setValidator($validator);      
        $databaseUserName = $helper->ask($input, $output, $question);

        $question = new Question("\t Enter database Password: ");
        $question->setValidator($validator);      
        $databasePassword = $helper->ask($input, $output, $question);

        $this->style->newLine();
        $question = new ConfirmationQuestion ("\t Start installation [yes]: ",true);
        $start = $helper->ask($input, $output, $question);     
        $this->style->newLine();

        if ($start == 1) {
            // run install
            
        }
        return true;
    }
}
