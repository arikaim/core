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
use Arikaim\Core\Module\ModulesManager;

class ModuleShowCommand extends ConsoleCommand
{  
    protected function configure()
    {
        $this->setName('module:show')->setDescription('Show module details');
        $this->addRequiredArgument('name');
    }

    protected function executeCommand($input, $output)
    {       
        $service_name = $input->getArgument('name');
        $this->style->text('Module ' . $service_name);
        $this->style->newLine();

        $modules_manager = new ModulesManager();
        $modules = $modules_manager->getModulesList();
        
        foreach ($modules as $module) {
            if ($module['service_name'] == $service_name) {
                $headers = [];
                $bootable = ($module['bootable'] == 1) ? "yes" : "no";
                $status = ($module['status'] == 1) ? "enabled" : "disabled";
                $rows = [
                    ['Title',$module['title']],
                    ['Description',$module['description']],
                    ['Version',$module['version']],
                    ['Service Name',$module['service_name']],
                    ['Class',$module['class']],
                    ['Path',$module['path']],
                    ['Bootable',$bootable],
                    ['Status',$status]
                ];
                $this->style->table($headers,$rows);
                return;
            }
        }
        $this->style->error("Unknow module '" . $service_name . "'.");
        $this->style->newLine();
    }    
}
