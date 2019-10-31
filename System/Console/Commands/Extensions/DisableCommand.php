<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\System\Console\Commands\Extensions;

use Arikaim\Core\System\Console\ConsoleCommand;
use Arikaim\Core\System\Console\ConsoleHelper;
use Arikaim\Core\Packages\Extension\ExtensionsManager;

/**
 * Disable extension
 */
class DisableCommand extends ConsoleCommand
{  
    /**
     * Configurecommand
     * name: extensions:disable [ext name]
     * @return void
     */
    protected function configure()
    {
        $this->setName('extensions:disable')->setDescription('Disable extension');
        $this->addOptionalArgument('name','Extension Name');
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
        $name = $input->getArgument('name');
        if (empty($name) == true) {
            $this->showError("Extension name required!");
            return;
        }
    
        $manager = new ExtensionsManager();
        $package = $manager->createPackage($name);
        if ($package == false) {
            $this->showError("Extension $name not exists!");
            return;
        }
        $installed = $package->getProperties()->get('installed');
       
        if ($installed == false) {
            $this->showError("Extension $name not installed!");
            return;
        }
        $result = $manager->disablePackage($name);
        if ($result == false) {
            $this->showError("Can't disable extension!");
            return;
        }
        $this->showCompleted();
    }
}
