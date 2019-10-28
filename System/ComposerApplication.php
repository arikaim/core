<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System;

use Arikaim\Core\System\Process;

/**
 * Composer commands
 */
class ComposerApplication
{   
    /**
     * Run require command
     *
     * @param string $packageName
     * @param boolean $async
     * @param boolean $realTimeOutput
     * @return mixed
     */
    public static function requireCommand($packageName, $async = false, $realTimeOutput = false)
    {
        return Self::runCommand("require $packageName",$async,$realTimeOutput); 
    }
    
    /**
     * Check if package is installed
     *
     * @param string $packageName
     * @param boolean $async
     * @param boolean $realTimeOutput
     * @return boolean
     */
    public static function hasPackage($packageName, $async = false, $realTimeOutput = false)
    {
        return Self::runCommand("show $packageName",$async,$realTimeOutput); 
    }

    /**
     * Run show command
     *
     * @param string $packageName
     * @param boolean $async
     * @param boolean $realTimeOutput
     * @return mixed
     */
    public static function show($packageName, $async = false, $realTimeOutput = false)
    {
        return Self::runCommand("show $packageName",$async,$realTimeOutput); 
    }

    /**
     * Run remove comand
     *
     * @param string $packageName
     * @param boolean $async
     * @param boolean $realTimeOutput
     * @return mixed
     */
    public static function remove($packageName,$async = false, $realTimeOutput = false)
    {
        return Self::runCommand("remove $packageName --no-dev",$async,$realTimeOutput); 
    }

    /**
     * Run update package command
     *
     * @param string $packageName
     * @param boolean $async
     * @param boolean $realTimeOutput
     * @return mixed
     */
    public static function updatePackage($packageName, $async = false, $realTimeOutput = false)
    {
        return Self::runCommand("update $packageName --no-dev",$async,$realTimeOutput);
    }

    /**
     * Run update command
     *
     * @param boolean $async
     * @param boolean $realTimeOutput
     * @return mixed
     */
    public static function update($async = false, $realTimeOutput = false)
    {
        return Self::runCommand('update --no-dev',$async,$realTimeOutput);
    }

    /**
     * Run composer command
     *
     * @param string $command
     * @param boolean $async
     * @param boolean $realTimeOutput
     * @return mixed
     */
    public static function runCommand($command, $async = false, $realTimeOutput = false)
    {
        $command = "php " . Path::ARIKAIM_BIN_PATH . 'composer.phar ' . $command;
        $env = [
            'COMPOSER_HOME'      => Path::ARIKAIM_BIN_PATH,
            'COMPOSER_CACHE_DIR' => '/dev/null'
        ];
        $process = Process::create($command,$env);
        try {
            if ($async == true) {
                $process->start();
            } else {
                if ($realTimeOutput == true) {
                    $process->run(function ($type, $buffer) {                       
                        echo $buffer;                        
                    });
                }
                $process->run();
            }
            $output = $process->getOutput();
        } catch(\Exception $e) {
            return false;
        }

        return $output;
    }
}
