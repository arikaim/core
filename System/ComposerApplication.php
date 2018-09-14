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

use Arikaim\Core\Arikaim;
use Arikaim\Core\System\SystemProcess;

class ComposerApplication
{   
    public function __construct() 
    {

    }

    public static function require($package_name,$async = false, $real_time_output = false)
    {
        return Self::runCommand("require $package_name",$async,$real_time_output); 
    }
    
    public static function hasPackage($package_name)
    {
    
    }

    public static function show($package_name,$async = false, $real_time_output = false)
    {
        return Self::runCommand("show $package_name",$async,$real_time_output); 
    }

    public static function remove($package_name,$async = false, $real_time_output = false)
    {
        return Self::runCommand("remove $package_name --no-dev",$async,$real_time_output); 
    }

    public static function updatePackage($package_name, $async = false, $real_time_output = false)
    {
        return Self::runCommand("update $package_name --no-dev",$async,$real_time_output);
    }

    public static function update($async = false, $real_time_output = false)
    {
        return Self::runCommand('update --no-dev',$real_time_output);
    }

    public static function runCommand($command, $async = false, $real_time_output = false)
    {
        $command = "php " . Self::getComposerPath() . 'composer.phar ' . $command;
        $env = ['COMPOSER_HOME' => Self::getComposerPath(),
                'COMPOSER_CACHE_DIR' => '/dev/null'];
        $process = SystemProcess::create($command,null,$env);
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

    public static function getComposerPath()
    {
        return Arikaim::getBinPath() . DIRECTORY_SEPARATOR;
    }
}