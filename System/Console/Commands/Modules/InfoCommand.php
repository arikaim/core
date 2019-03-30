<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System\Console\Commands\Modules;

use Symfony\Component\Console\Helper\Table;
use Arikaim\Core\System\Console\ConsoleHelper;
use Arikaim\Core\System\Console\ConsoleCommand;
use Arikaim\Core\Packages\Module\ModulesManager;

class InfoCommand extends ConsoleCommand
{  
    protected function configure()
    {
        $this->setName('modules:info')->setDescription('Show module details');
        $this->addOptionalArgument('name','Module Name');
    }

    protected function executeCommand($input, $output)
    {       
        $name = $input->getArgument('name');
        $this->style->text('Module ' . $name);
        $this->style->newLine();

        $table = new Table($output);
        $table->setHeaders(['','']);
        $table->setStyle('compact');

        $manager = new ModulesManager();
        $package = $manager->createPackage($name);
        if ($package == false) {
            $this->showError("Module $name not exists!");
            return;
        }
        $module = $package->getProperties(true);

        $bootable = ($module['bootable'] == 1) ? "yes" : "no";
        $installed = ($module['installed'] == true) ? "yes" : "no";
        $status = ($module['status'] == 1) ? "enabled" : "disabled";
        $rows = [
            ['Title',$module['title']],
            ['Description',$module['description']],
            ['Version',$module['version']],
            ['Service Name',$module['service_name']],
            ['Class',$module['class']],
            ['Bootable',$bootable],
            ['Type',$module['type']],
            ['Status',$status],
            ['Installed',$installed]
        ];

        $table->setRows($rows);
        $table->render();
        $this->style->newLine();
    }    
}