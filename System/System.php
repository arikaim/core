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

use Illuminate\Database\Capsule\Manager;
use Arikaim\Core\Arikaim;

class System 
{
    const UNKNOWN = 1;
    const WINDOWS = 2;
    const LINUX   = 3;
    const OSX     = 4;

    const LF = "\n";
    const CRLF = "\r\n";
    const CR = "\r";
    const HTMLLF = "</br>";

    public static function getVersion() 
    {
        return ARIKAIM_VERSION;
    }

    public static function getSystemInfo() 
    {     
        $info['cms_version'] = Self::getVersion(); 
        $info['php_version'] = Self::getPhpVersion();
        $os = posix_uname();
        $info['os_name'] = explode(' ',$os['sysname'])[0];
        $info['os_version'] = $os['release'];
        $info['os_machine'] = $os['machine'];
        $info['os_node'] = $os['nodename'];
        $info['database'] = Self::getDatabaseInfo();
        return $info;
    }

    public static function getVersionText($text)
    {
        $items = explode('.',$text);
        if (isset($items[0]) == true) {
            $version = $items[0];
        } else {
            return "";
        }
        if (isset($items[1]) == true) {
            $version .= "." . $items[1];
        }
        if (isset($items[2]) == true) {
            $version .= "." . substr($items[2],0,2);
        }
        return $version;
    }

    /**
     * Set script execution tile limit (0 - unlimited)
     *
     * @param number $time
     * @return boolean
     */
    public static function setTimeLimit($time)
    {
        if (is_numeric($time) == true) {
            set_time_limit($time);
            return true;
        }
        return false;
    }

    public static function getPhpVersion()
    {                   
        return Self::getVersionText(phpversion());
    }
   
    public function getPhpExtensions()
    {
        $data = [];
        $items = get_loaded_extensions(false);
        foreach ($items as $item) {
            $version = Self::getVersionText(Self::getPhpExtensionVersion($item));            
            array_push($data,['name' => $item,'version' => $version]);
        }
        return $data;
    }

    public static function getPhpExtensionVersion($php_extension_name)
    {
        $ext = new \ReflectionExtension($php_extension_name);
        return $ext->getVersion();
    }

    public static function hasPhpExtension($php_extension_name) 
    {
        return extension_loaded($php_extension_name);
    }

    public static function getDatabaseInfo() 
    {        
        if (Self::hasPhpExtension('PDO') == true) {
            $pdo = Manager::connection()->getPdo();
            $driver_name = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
            $server_info = $pdo->getAttribute(\PDO::ATTR_SERVER_INFO); 
            $version = substr($pdo->getAttribute(\PDO::ATTR_SERVER_VERSION),0,6); 
        } else {
            $driver_name = "";
            $server_info = "";
            $version = "";
            $name = "";
        }
        
        $info['driver'] = $driver_name;
        $info['server_info'] = $server_info;
        $info['version'] = $version;
        $info['name'] = Arikaim::config('db/database');
        return $info;
    }
    
    public static function hasPdoDriver($driver_name)
    {
        $drivers = Self::getPdoDrivers();
        if (is_array($drivers) == true) {
            return in_array($driver_name,$drivers);  
        }         
        return false;
    }

    public static function getPdoDrivers()
    {
        if (Self::hasPhpExtension('PDO') == false) {
            return [];
        }
        return \PDO::getAvailableDrivers();
    }

    public static function checkSystemRequirements()
    {
        $info['items'] = [];
        $info['errors']['messages'] = "";
        $errors = [];

        // php 5.6 or above
        $php_version = Self::getPhpVersion();
        $item['message'] = "PHP $php_version";
        $item['status'] = 0; // error   
        if (version_compare($php_version,'5.6','>=') == true) {               
            $item['status'] = 1; // ok                    
        } else {
            array_push($errors,Arikaim::errors()->getError("PHP_VERSION_ERROR"));
        }
        array_push($info['items'],$item);

        // PDO extension
        $item['message'] = 'PDO php extension';
        $item['status'] = 0; // error
        if (Self::hasPhpExtension('PDO') == true) {
            $item['status'] = 1; // ok 
        }
        array_push($info['items'],$item);

        // PDO driver
        $pdo_driver = Arikaim::config('db/driver');
        $item['message'] = "$pdo_driver PDO driver";
        $item['status'] = 0; // error
        if (Self::hasPdoDriver($pdo_driver) == true) {
            $item['status'] = 1; // ok
        } else {
            array_push($errors,Arikaim::errors()->getError("PDO_ERROR"));         
        }
        array_push($info['items'],$item);

        // curl extension
        $item['message'] = 'Curl PHP extension';
        $item['status'] = 2; // warning
        if (Self::hasPhpExtension('curl') == true) {
            $item['status'] = 1; // ok 
        }
        array_push($info['items'],$item);

        // zip extension
        $item['message'] = 'Zip PHP extension';
        $item['status'] = 2; // warning
        if (Self::hasPhpExtension('zip') == true) {
            $item['status'] = 1; // ok
        }
        array_push($info['items'],$item);
        
        // GD extension 
        $item['message'] = 'GD PHP extension';
        $item['status'] = 2; // warning
        if (Self::hasPhpExtension('gd') == true) {
            $item['status'] = 1; // ok
        }
        array_push($info['items'],$item);

        $info['errors'] = $errors;
        return $info;
    }  

    public static function getStreamWrappers()
    {
        return stream_get_wrappers();
    }

    public static function hasStreamWrapper($protocol)
    {
        $items = Self::getStreamWrappers();
        return in_array($protocol,$items);
    }

    public static function getBacktrace()
    {
        return debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT);
    }

    public static function isConsole()
    {
        return (php_sapi_name() == "cli") ? true : false;          
    }   

    public static function writeLine($text, $eof = null)
    {
        if ($eof == null) {
            $eof = Self::getEof();
        }
        echo $text . $eof;
    }

    public static function getEof() 
    { 
        $os = Self::getOS();
        switch ($os) {
            case Self::WINDOWS: {
                return Self::CRLF;
            }
            case Self::LINUX: {
                return Self::LF;
            }
            case Self::OSX: {
                return Self::CR;
            }
            default: {
                return Self::LF;
            }
        }
    }

    public static function getOS() 
    {
        switch (true) {
            case stristr(PHP_OS, 'DAR'): {
                return Self::OSX;
            }
            case stristr(PHP_OS, 'WIN'): {
                return Self::WINDOWS;
            }
            case stristr(PHP_OS, 'LINUX'): {
                return Self::LINUX;
            }
            default: {
                return Self::UNKNOWN;
            }
        }
    }

    public static function getScriptPath()
    {
        return realpath(dirname(__FILE__));
    }
    
    public static function getConfig($variable_name)
    {
        $allowed = ['cors','debug','debugTrace'];
        if (in_array($variable_name,$allowed) == true) {
            return Arikaim::config("settings/$variable_name");
        }
        return null;
    }

    public static function getCorePackageName()
    {
        return "arikaim/core";
    }
}
