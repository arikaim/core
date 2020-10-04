<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\App\Commands\Install;

use Symfony\Component\Console\Output\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Arikaim\Core\Console\ConsoleCommand;
use Arikaim\Core\Console\ConsoleHelper;
use Arikaim\Core\App\Install;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Utils\File;
use Arikaim\Core\Utils\Path;

/**
 * Prepare install command class
 */
class PrepareCommand extends ConsoleCommand
{  
    /**
     * Command config
     * name: install
     * @return void
     */
    protected function configure()
    {
        $this->setName('install:prepare')->setDescription('Check install requirements.');
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
        $this->showTitle('Check instalation requirements');
      
        //Requirements             
        $requirements = Install::checkSystemRequirements();

        // status - 0 red , 1 - ok,  2 - oarange
        foreach ($requirements['items'] as $item) {
            if ($item['status'] == 1) {
                $label = "\t" . ConsoleHelper::checkMark() . ' ' . ConsoleHelper::getLabelText($item['message'],'green');               
            } else {
                $label = "\t" . ' ' . ConsoleHelper::getLabelText($item['message'],'red');    
            }
            $this->style->writeLn($label);
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
        $this->style->write("\t Set cache directory writable ");
        $result = File::setWritable(Path::CACHE_PATH); 
        if ($result == true) {
            $this->style->write(ConsoleHelper::getLabelText("\t done"));
        } else {
            $this->showError("Error: Can't set cache folder writtable.");
            return;
        }

        $this->style->newLine();
        $this->style->write("\t Set config file writable ");
        $result = File::setWritable(Arikaim::config()->getConfigFile());
        if ($result == true) {
            $this->style->write(ConsoleHelper::getLabelText("\t done"));
        } else {
            $this->showError("Error: Can't set config file writtable.");
            return;
        };

        $this->style->newLine();
        $this->style->write("\t Set config relations file writable ");
        $result = File::setWritable(Path::CONFIG_PATH . 'relations.php');
        if ($result == true) {
            $this->style->write(ConsoleHelper::getLabelText("\t done"));
        } else {
            $this->showError("Error: Can't set config relations file writtable.");
            return;
        };

        $this->style->newLine();       
        $this->style->newLine();       
    }
}
