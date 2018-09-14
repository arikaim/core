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

class ModulesListCommand extends ConsoleCommand
{  
    protected function configure()
    {
        $this->setName('modules:list')->setDescription('Show Arikaim core modules');
    }

    protected function executeCommand($input, $output)
    {       
        $this->style->text('Modules');
        $this->style->newLine();

        $modules_manager = new ModulesManager();
        $modules = $modules_manager->getModulesList();

        foreach ($modules as $module) {
            $this->style->text($module['service_name'] . " " . $module['version']);
        }
        $this->style->newLine();
    }    
}
