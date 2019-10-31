<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\System\Console\Commands\Extensions;

use Symfony\Component\Console\Helper\Table;
use Arikaim\Core\System\Console\ConsoleCommand;
use Arikaim\Core\System\Console\ConsoleHelper;
use Arikaim\Core\Packages\Extension\ExtensionsManager;

/**
 * Extension info command
 */
class InfoCommand extends ConsoleCommand
{  
    /**
     * Configure command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('extensions:info')->setDescription('Extension Info');
        $this->addOptionalArgument('name','Extension Name');
    }

    /**
     * Run command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function executeCommand($input, $output)
    {       
        $table = new Table($output);
        $table->setHeaders(['', '']);
        $table->setStyle('compact');


        $name = $input->getArgument('name');
        if (empty($name) == true) {
            $this->showError("Extension name required!");
            return;
        }
    
        $manager = new ExtensionsManager();
        $package = $manager->createPackage($name);
        if ($package == false) {
            $this->showError("Extension $name not exists!");
            return;
        }
        $extension = $package->getProperties();

        $this->showTitle('Extension ' . ConsoleHelper::getLabelText($name,'green'));
        $this->style->writeLn(ConsoleHelper::getDescriptionText($extension->description)); 
       
        $rows = [
            ['Version',$extension->version],
            ['Class',$extension->class],
            ['Status',ConsoleHelper::getStatusText($extension->status)],
            ['Installed',ConsoleHelper::getYesNoText($extension->installed)]
        ];
            
        $table->setRows($rows);
        $table->render();
        $this->style->newLine();
    }
}
