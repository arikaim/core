<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System\Cli\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Style\SymfonyStyle;

class HelpCommand extends Command
{  
    protected function configure()
    {
        $this->setName('help')
        ->setDescription('Arikaim Cli Help');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $style = new SymfonyStyle($input, $output);
        $style->title('Arikaim Cli Help');

        $helper = new DescriptorHelper();
        $helper->describe($output, $this->getApplication(), array(
            
        ));
    }
}
