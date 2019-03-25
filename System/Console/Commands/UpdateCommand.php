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


class UpdateCommand extends ConsoleCommand
{  
    protected function configure()
    {
        $this->setName('update')->setDescription('Arikaim Update');
    }

    protected function executeCommand($input, $output)
    {
        $this->style->writeLn('Update');
    
        $result = ComposerApplication::updatePackage(System::getCorePackageName(),false,true);
        return $result;
    }
}
