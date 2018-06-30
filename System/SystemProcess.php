<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System;

use Symfony\Component\Process\Process;

/**
 * System Process
 */
class SystemProcess 
{
    public static function runComposerCommand($command)
    {        
        $process = Self::create('composer ' . $command);
        try {
            $process->mustRun();
            $output = $process->getOutput();
        } catch(\Exception $e) {
            return "Errror:" . $e->getMessage();
        }
        return $output;
    }

    public static function create($command, $cwd = null, array $env = null, $input = null, $timeout = 60, array $options = array())
    {
        return new Process($command,$cwd,$env,$input,$timeout,$options);
    }
}
