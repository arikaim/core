<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System\Console;

use Symfony\Component\Console\Application as ConsoleApplication;
use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Db\Model;
use Arikaim\Core\System\System;

class Application
{       
    protected $applicatgion;

    protected $commands = [
        "Arikaim\\Core\\System\\Console\\Commands\\HelpCommand",
        "Arikaim\\Core\\System\\Console\\Commands\\UpdateCommand",
        "Arikaim\\Core\\System\\Console\\Commands\\InstallCommand",
        "Arikaim\\Core\\System\\Console\\Commands\\CacheCommand",
        "Arikaim\\Core\\System\\Console\\Commands\\ShellCommand",
        'Arikaim\\Core\\System\\Console\\Commands\\QueueWorkerCommand',
        // extensions
        "Arikaim\\Core\\System\\Console\\Commands\\Extensions\\InfoCommand",
        "Arikaim\\Core\\System\\Console\\Commands\\Extensions\\ListCommand",  
        "Arikaim\\Core\\System\\Console\\Commands\\Extensions\\UnInstallCommand",      
        "Arikaim\\Core\\System\\Console\\Commands\\Extensions\\EnableCommand",    
        "Arikaim\\Core\\System\\Console\\Commands\\Extensions\\DisableCommand",       
        "Arikaim\\Core\\System\\Console\\Commands\\Extensions\\InstallCommand",
        // modules
        "Arikaim\\Core\\System\\Console\\Commands\\Modules\\ListCommand",
        "Arikaim\\Core\\System\\Console\\Commands\\Modules\\InfoCommand",
        // UI library
        "Arikaim\\Core\\System\\Console\\Commands\\Library\\ListCommand",
        // templates
        "Arikaim\\Core\\System\\Console\\Commands\\Template\\ListCommand",
    ];

    public function __construct() 
    {
        $this->application = new ConsoleApplication("\nArikaim Cli ",System::getVersion());    
        // add core commands 
        $this->addCommands($this->commands);
        // add extensions commands
        $this->loadExtensionsCommands();
        // add modules commands
        $this->loadModulesCommands();
    }

    /**
     * Run console cli
     *
     * @return void
     */
    public function run()
    {

        $this->application->run();
    }

    public function loadExtensionsCommands()
    {
        $extensions = Model::Extensions()->getActive()->get();
        foreach ($extensions as $extension) {
            $this->addCommands($extension->console_commands);
        }
    }

    public function loadModulesCommands()
    {
    }

    public function addCommands(array $commands)
    {
        foreach ($commands as $class) {
            $command = Factory::createInstance($class);
            if (is_object($command) == true) {
                $this->application->add($command);
                if ($command->isDefault() == true) {
                    $this->application->setDefaultCommand($command->getName());
                }
            }
        }
    }
}
