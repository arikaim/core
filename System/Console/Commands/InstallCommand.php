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
use Arikaim\Core\System\ComposerApplication;
use Arikaim\Core\System\System;
use Arikaim\Core\System\Install;

class InstallCommand extends ConsoleCommand
{  
    protected function configure()
    {
        $this->setName('install')->setDescription('Arikaim Install');
    }

    protected function executeCommand($input, $output)
    {
        $this->style->writeLn('Install');
      
        if (Install::isInstalled() == true) {
            $this->style->text('Arikaim CMS already installed.');
            $this->style->newLine();
            return true;
        }
        return true;
    }
}
