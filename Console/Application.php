<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\Console;

use Symfony\Component\Console\Application as ConsoleApplication;

use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Db\Model;
use Arikaim\Core\System\System;
use Arikaim\Core\Arikaim;
use Arikaim\Core\App\Install;

/**
 * Console application
 */
class Application
{       
    /**
     * App object
     *
     * @var Symfony\Component\Console\Application
     */
    protected $applicatgion;

    /**
     * Constructor
     */
    public function __construct() 
    {
        // add core command classes
        $this->commands = Arikaim::config()->load('console.php');

        $this->application = new ConsoleApplication("\nArikaim Cli ",System::getVersion());    
        // add core commands 
        $this->addCommands($this->commands);
       
        if (Arikaim::db()->isValidPdoConnection() == true) {
            if (Install::isInstalled() == true) {
                // add extensions commands
                $this->loadExtensionsCommands();
                // add modules commands
                $this->loadModulesCommands();
            }
        }
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

    /**
     * Load extensins commands.
     *
     * @return void
     */
    public function loadExtensionsCommands()
    {
        $extensions = Arikaim::packages()->create('extension')->getPackgesRegistry()->getPackagesList([
            'status' => 1    
        ]); 

        foreach ($extensions as $extension) {
            $this->addCommands($extension->console_commands);
        }
    }

    /**
     * Load modules commands
     *
     * @return void
     */
    public function loadModulesCommands()
    {
        $modules = Arikaim::packages()->create('module')->getPackgesRegistry()->getPackagesList([
            'status' => 1    
        ]);         
        foreach ($modules as $module) {
            $this->addCommands($module['console_commands']);
        }
    }

    /**
     * Add commands to console app
     *
     * @param array $commands
     * @return void
     */
    public function addCommands($commands)
    {
        if (is_array($commands) == false) {
            return false;
        }
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