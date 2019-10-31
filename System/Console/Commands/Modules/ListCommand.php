<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\System\Console\Commands\Modules;

use Symfony\Component\Console\Helper\Table;
use Arikaim\Core\System\Console\ConsoleHelper;
use Arikaim\Core\System\Console\ConsoleCommand;
use Arikaim\Core\Packages\Module\ModulesManager;

/**
 * Modules list command
 */
class ListCommand extends ConsoleCommand
{  
    /**
     * Configure command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('modules:list')->setDescription('Show modules list.');
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

            $installedLabel = ($module->installed == true) ?  ConsoleHelper::getLabelText('installed','cyan') : '';
            $statusLabel = ($module->status == 1) ?  ConsoleHelper::getLabelText('enabled','green') : '';
            $label = $installedLabel . " " . $statusLabel;
            $row = [$module->name,$module->version,$module->type,$label];
            array_push($rows,$row);
        }

        $table->setRows($rows);
        $table->render();
        $this->style->newLine();
    }    
}
