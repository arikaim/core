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

class ListCommand extends ConsoleCommand
{  
    protected function configure()
    {
        $this->setName('modules:list')->setDescription('Show modules list.');
    }

    protected function executeCommand($input, $output)
    {       
        $this->showTitle('Modules');
        
        $table = new Table($output);
        $table->setHeaders(['Name','Version','Type','Status']);
        $table->setStyle('compact');

        $manager = new ModulesManager();
        $items = $manager->getPackages();

        $rows = [];
        foreach ($items as $name) {
            $package = $manager->createPackage($name);
            $module = $package->getProperties(true);

            $installed_label = ($module->installed == true) ?  ConsoleHelper::getLabelText('installed','cyan') : '';
            $status_label = ($module->status == 1) ?  ConsoleHelper::getLabelText('enabled','green') : '';
            $label = $installed_label . " " . $status_label;
            $row = [$module->name,$module->version,$module->type,$label];
            array_push($rows,$row);
        }

        $table->setRows($rows);
        $table->render();
        $this->style->newLine();
    }    
}
