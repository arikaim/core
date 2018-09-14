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
use Arikaim\Core\Extension\ExtensionsManager;

class ExtensionsListCommand extends ConsoleCommand
{  
    protected function configure()
    {
        $this->setName('extensions:list')->setDescription('Extensions list');
    }

    protected function executeCommand($input, $output)
    {       
        $this->style->text('Extensions List');
        $this->style->newLine();
        $manager = new ExtensionsManager();
        $extensions = $manager->getExtensions();
        foreach ($extensions as $item) {
            $this->style->text($item['name'] . " v" . $item['version']);
        }
        $this->style->newLine();
    }
}
