<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System\Console\Commands\Library;

use Symfony\Component\Console\Helper\Table;
use Arikaim\Core\System\Console\ConsoleCommand;
use Arikaim\Core\System\Console\ConsoleHelper;
use Arikaim\Core\Packages\Library\LibraryManager;

/**
 * Library list command
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
        $this->setName('library:list')->setDescription('UI library list');
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
        $this->showTitle('UI library');
      
        $table = new Table($output);
        $table->setHeaders(['Name', 'Version', 'Type']);
        $table->setStyle('compact');

        $manager = new LibraryManager();
        $items = $manager->getPackages();

        $rows = [];
        foreach ($items as $library_name) {
            $library_package = $manager->createPackage($library_name);
            $library = $library_package->getProperties();
            $label = ($library->framework == true) ?  ConsoleHelper::getLabelText('framework','cyan') : '';
            $row = [$library->name,$library->version,$label];
            array_push($rows,$row);
        }

        $table->setRows($rows);
        $table->render();
        $this->style->newLine();
    }
}
