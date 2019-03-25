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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;

use Arikaim\Core\System\System;

abstract class ConsoleCommand extends Command
{       
    protected $style;
    protected $default;
   
    public function __construct($name = null) 
    {
        parent::__construct($name);
    }

    public function run(InputInterface $input,OutputInterface $output)
    {
        $this->style = new SymfonyStyle($input, $output);
        return parent::run($input, $output);
    }

    public function addRequiredArgument($name, $description = '', $default = null)
    {
        $this->addArgument($name,InputArgument::REQUIRED,$description,$default);
    }

    public function addOptionalArgument($name, $description = '', $default = null)
    {
        $this->addArgument($name,InputArgument::OPTIONAL,$description,$default);
    }

    abstract protected function executeCommand($input,$output);
   
    protected function execute(InputInterface $input,OutputInterface $output)
    {
        $this->executeCommand($input,$output);
    }

    public function setDefault($default = true)
    {
        $this->default = $default;
    }

    public function isDefault()
    {
        return $this->default;
    }

    public function showTitle($title)
    {
        $this->style->newLine();
        $this->style->writeLn($title);
        $this->style->newLine();
    }

    public function showError($message, $label = "Error:")
    {
        $this->style->newLine();
        $this->style->writeLn("<error>$label $message</error>");
        $this->style->newLine();
    }

    public function showCompleted($label = null)
    {
        if ($label == null) {
            $label = 'done.';
        }
        $this->style->newLine();
        $this->style->writeLn("<fg=green>$label</>");
    }
}
