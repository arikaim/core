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
        "Arikaim\\Core\\System\\Console\\Commands\\ExtensionsListCommand",
        "Arikaim\\Core\\System\\Console\\Commands\\ModulesListCommand",
        "Arikaim\\Core\\System\\Console\\Commands\\ModuleShowCommand",
        "Arikaim\\Core\\System\\Console\\Commands\\UpdateCommand"
    ];

    public function __construct() 
    {
        $this->application = new ConsoleApplication("\nArikaim Cli ",System::getVersion());      
        // add core commands 
        $this->addCommands($this->commands);
        // add extensions commands
        $this->loadExtensionsCommands();
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

    protected function loadExtensionsCommands()
    {
        $extensions = Model::Extensions()->getActive()->get();
        foreach ($extensions as $extension) {
            $this->addCommands($extension->console_commands);
        }
    }

    protected function addCommands(array $commands)
    {
        foreach ($commands as $class) {
            $command = Factory::createInstance($class);
            if (is_object($command) == true) {
                $this->application->add($command);
            }
        }
    }
}
