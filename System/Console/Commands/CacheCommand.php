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
use Arikaim\Core\Arikaim;


class CacheCommand extends ConsoleCommand
{  
    protected function configure()
    {
        $this->setName('cache:clear')->setDescription('Clear cache');
    }

    protected function executeCommand($input, $output)
    {
        $this->showTitle('Clear cache.');
      
        $result = Arikaim::cache()->clear();

        $this->showCompleted();
        return $result;
    }
}
