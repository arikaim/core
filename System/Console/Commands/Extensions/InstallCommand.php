<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System\Console\Commands\Extensions;

use Arikaim\Core\System\Console\ConsoleCommand;
use Arikaim\Core\System\Console\ConsoleHelper;
use Arikaim\Core\Packages\Extension\ExtensionsManager;

class InstallCommand extends ConsoleCommand
{  
    protected function configure()
    {
        $this->setName('extensions:install')->setDescription('Install extension');
        $this->addOptionalArgument('name','Extension Name');
    }

    protected function executeCommand($input, $output)
    {       
        $name = $input->getArgument('name');
        if (empty($name) == true) {
            $this->showError("Extension name required!");
            return;
        }
    
        $manager = new ExtensionsManager();
        $result = $manager->installPackage($name);
     
        if ($result == false) {
            $this->showError("Can't install extension!");
            return;
        }

        $this->showCompleted();
    }
}
