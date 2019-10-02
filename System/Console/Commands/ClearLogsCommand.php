<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System\Console\Commands;

use Arikaim\Core\System\Console\ConsoleCommand;
use Arikaim\Core\Arikaim;

/**
 *  Clear Logs command
 */
class ClearLogsCommand extends ConsoleCommand
{  
    /**
     * Configure command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('logs:clear')->setDescription('Clear logs');
    }

    /**
     * Command code
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    protected function executeCommand($input, $output)
    {
        $this->style->writeLn('Clear Logs');
    
        $result = Arikaim::logger()->deleteSystemLogs();
        if ($result == true) {
            $this->showCompleted();
        } else {
            $this->showError("Can't remove logs file!");
        }
    }
}