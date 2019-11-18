<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\System\Console\Commands\Template;

use Arikaim\Core\System\Console\ConsoleCommand;
use Arikaim\Core\Packages\Template\TemplatesManager;

/**
 * Install theme command
 */
class InstallCommand extends ConsoleCommand
{  
    /**
     * Configure command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('theme:install')->setDescription('Install theme');
        $this->addOptionalArgument('name','Theme Name');
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
            $this->showError("Theme name required!");
            return;
        }
    
        $manager = new TemplatesManager();
        $result = $manager->installPackage($name);
     
        if ($result == false) {
            $this->showError("Can't install theme!");
            return;
        }

        $this->showCompleted();
    }
}
