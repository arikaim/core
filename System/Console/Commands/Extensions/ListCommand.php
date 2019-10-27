<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System\Console\Commands\Extensions;

use Symfony\Component\Console\Helper\Table;
use Arikaim\Core\System\Console\ConsoleCommand;
use Arikaim\Core\System\Console\ConsoleHelper;
use Arikaim\Core\Packages\Extension\ExtensionsManager;

/**
 * Extensions list command
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
        $this->setName('extensions:list')->setDescription('Extensions list');
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
        $table->setHeaders(['Name', 'Version', 'Status','Installed']);
        $table->setStyle('compact');

        $this->showTitle('Extensions');
     
        $manager = new ExtensionsManager();
        $items = $manager->getPackages();
        
        $rows = [];
        foreach ($items as $name) {
            $package = $manager->createPackage($name);
            $extension = $package->getProperties();

            $status = ConsoleHelper::getStatusText($extension->status);
            $installed = ConsoleHelper::getYesNoText($extension->installed);

            $row = [$extension->name,$extension->version,$status,$installed];
            array_push($rows,$row);
        }
        
        $table->setRows($rows);
        $table->render();
        $this->style->newLine();
    }
}
