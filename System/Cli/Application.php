<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System\Cli;

use Symfony\Component\Console\Application as ConsoleApplication;
use Arikaim\Core\System\Cli\Commands\DefaultCommand;
use Arikaim\Core\System\Cli\Commands\HelpCommand;
use Arikaim\Core\System\Cli\Commands\CreateExtensionCommand;

class Application
{       
    protected $applicatgion;

    public function __construct() 
    {
        $this->application = new ConsoleApplication();      
        // add commands 
        $this->application->add(new HelpCommand());
        $this->application->add(new CreateExtensionCommand());

        // default command
        $default_command = new DefaultCommand();
        $this->application->add($default_command);
        $this->application->setDefaultCommand($default_command->getName());
    }

    public function run()
    {
        $this->application->run();
    }
}
