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

use Arikaim\Core\Arikaim;
use Arikaim\Core\System\Process;

/**
 * Composer commands
 */
class ComposerApplication
{   
    /**
     * Run require command
     *
     * @param string $package_name
     * @param boolean $async
     * @param boolean $real_time_output
     * @return mixed
     */
    public static function requireCommand($package_name, $async = false, $real_time_output = false)
    {
        return Self::runCommand("require $package_name",$async,$real_time_output); 
    }
    
    /**
     * Check if package is installed
     *
     * @param string $package_name
     * @param boolean $async
     * @param boolean $real_time_output
     * @return boolean
     */
    public static function hasPackage($package_name, $async = false, $real_time_output = false)
    {
        return Self::runCommand("show $package_name",$async,$real_time_output); 
    }

    /**
     * Run show command
     *
     * @param string $package_name
     * @param boolean $async
     * @param boolean $real_time_output
     * @return mixed
     */
    public static function show($package_name, $async = false, $real_time_output = false)
    {
        return Self::runCommand("show $package_name",$async,$real_time_output); 
    }

    /**
     * Run remove comand
     *
     * @param string $package_name
     * @param boolean $async
     * @param boolean $real_time_output
     * @return mixed
     */
    public static function remove($package_name,$async = false, $real_time_output = false)
    {
        return Self::runCommand("remove $package_name --no-dev",$async,$real_time_output); 
    }

    /**
     * Run update package command
     *
     * @param string $package_name
     * @param boolean $async
     * @param boolean $real_time_output
     * @return mixed
     */
    public static function updatePackage($package_name, $async = false, $real_time_output = false)
    {
        return Self::runCommand("update $package_name --no-dev",$async,$real_time_output);
    }

    /**
     * Run update command
     *
     * @param boolean $async
     * @param boolean $real_time_output
     * @return mixed
     */
    public static function update($async = false, $real_time_output = false)
    {
        return Self::runCommand('update --no-dev',$real_time_output);
    }

    /**
     * Run composer command
     *
     * @param string $command
     * @param boolean $async
     * @param boolean $real_time_output
     * @return mixed
     */
    public static function runCommand($command, $async = false, $real_time_output = false)
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
                if ($real_time_output == true) {
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
