<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System\Console\Commands\Template;

use Symfony\Component\Console\Helper\Table;
use Arikaim\Core\System\Console\ConsoleCommand;
use Arikaim\Core\System\Console\ConsoleHelper;
use Arikaim\Core\Packages\Template\TemplatesManager;

/**
 * Templates list command
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
        $this->setName('theme:list')->setDescription('Themes list');
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
        $this->showTitle('Themes');
      
        $table = new Table($output);
        $table->setHeaders(['Name', 'Version','Status']);
        $table->setStyle('compact');

        $manager = new TemplatesManager();
        $items = $manager->getPackages();

        $rows = [];
        foreach ($items as $template_name) {
            $package = $manager->createPackage($template_name);
            $template = $package->getProperties();
            $label = ($template->current == true) ? ConsoleHelper::getLabelText('current','cyan') : '';
            $row = [$template->name,$template->version,$label];

            array_push($rows,$row);
        }

        $table->setRows($rows);
        $table->render();
        $this->style->newLine();
    }
}
