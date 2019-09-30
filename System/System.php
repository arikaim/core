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

use Illuminate\Database\Capsule\Manager;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Utils\Utils;

/**
 *Core system helper class
 */
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

    /**
     * Return Arikaim version
     *
     * @return string
     */
    public static function getVersion() 
    {
        return ARIKAIM_VERSION;
    }

    /**
     * Get system info
     *
     * @return array
     */
    public static function getSystemInfo() 
    {  
        $os = posix_uname();   
        $info = [
            'cms_version' => Self::getVersion(), 
            'php_version' => Self::getPhpVersion(),       
            'os_name'     => explode(' ',$os['sysname'])[0],
            'os_version'  => $os['release'],
            'os_machine'  => $os['machine'],
            'os_node'     => $os['nodename'],
            'database'    => Self::getDatabaseInfo()
        ];
        return $info;
    }

    /**
     * Set script execution tile limit (0 - unlimited)
     *
     * @param integer $time
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

    /**
     * Return php version
     *
     * @return string
     */
    public static function getPhpVersion()
    {                   
        return substr(phpversion(),0,6);
    }
   
    /**
     * Return php extensions list
     *
     * @return array
     */
    public function getPhpExtensions()
    {
        $data = [];
        $items = get_loaded_extensions(false);
        foreach ($items as $item) {
            $version = Utils::formatVersion(Self::getPhpExtensionVersion($item));   
            array_push($data,['name' => $item,'version' => $version]);
        }
        return $data;
    }

    /**
     * Return php extension version
     *
     * @param string $php_extension_name
     * @return string
     */
    public static function getPhpExtensionVersion($php_extension_name)
    {
        $ext = new \ReflectionExtension($php_extension_name);
        return substr($ext->getVersion(),0,6);
    }

    /**
     * Return true if php extension is instaed
     *
     * @param string $php_extension_name
     * @return boolean
     */
    public static function hasPhpExtension($php_extension_name) 
    {
        return extension_loaded($php_extension_name);
    }

    /**
     * Get database info
     *
     * @return array
     */
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
        
        $info = [
            'driver'      => $driver_name,
            'server_info' => $server_info,
            'version'     => $version,
            'name'        => Arikaim::config('db/database')
        ];

        return $info;
    }
    
    /**
     * Return true if PDO driver is installed
     *
     * @param string $driver_name
     * @return boolean
     */
    public static function hasPdoDriver($driver_name)
    {
        $drivers = Self::getPdoDrivers();
        return (is_array($drivers) == true) ? in_array($driver_name,$drivers) : false;        
    }

    /**
     * Return PDO drivers list
     *
     * @return array
     */
    public static function getPdoDrivers()
    {
        return (Self::hasPhpExtension('PDO') == false) ? [] : \PDO::getAvailableDrivers();
    }

    /**
     * Verify system requirements
     *
     * @return array
     */
    public static function checkSystemRequirements()
    {
        $info['items'] = [];
        $info['errors']['messages'] = "";
        $errors = [];

        // php 5.6 or above
        $php_version = Self::getPhpVersion();
        $item['message'] = "PHP $php_version";
        $item['status'] = 0; // error   
        if (version_compare($php_version,'7.1','>=') == true) {               
            $item['status'] = 1; // ok                    
        } else {
            array_push($errors,Arikaim::errors()->getError("PHP_VERSION_ERROR"));
        }
        array_push($info['items'],$item);

        // PDO extension
        $item['message'] = 'PDO php extension';     
        $item['status'] = (Self::hasPhpExtension('PDO') == true) ? 1 : 0;
         
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
        $item['status'] = (Self::hasPhpExtension('curl') == true) ? 1 : 2;
           
        array_push($info['items'],$item);

        // zip extension
        $item['message'] = 'Zip PHP extension';    
        $item['status'] = (Self::hasPhpExtension('zip') == true) ? 1 : 2;

        array_push($info['items'],$item);
        
        // GD extension 
        $item['message'] = 'GD PHP extension';      
        $item['status'] = (Self::hasPhpExtension('gd') == true) ? 1 : 2;
          
        array_push($info['items'],$item);

        $info['errors'] = $errors;
        return $info;
    }  

    /**
     * Return Stream wrappers
     *
     * @return array
     */
    public static function getStreamWrappers()
    {
        return stream_get_wrappers();
    }

    /**
     * Return true if stream wrapper are installed
     *
     * @param string $protocol
     * @return boolean
     */
    public static function hasStreamWrapper($protocol)
    {      
        return in_array($protocol,Self::getStreamWrappers());
    }

    /**
     * Get debug backtrace
     *
     * @return array
     */
    public static function getBacktrace()
    {
        return debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT);
    }

    /**
     * Return true if script is run in console
     *
     * @return boolean
     */
    public static function isConsole()
    {
        return (php_sapi_name() == "cli") ? true : false;          
    }   

    /**
     * Output text
     *
     * @param string $text
     * @param string $eof
     * @return void
     */
    public static function writeLine($text, $eof = null)
    {
        $eof = ($eof == null) ? Self::getEof() : $eof;
        echo $text . $eof;
    }

    /**
     * Return EOF for current OS
     *
     * @return string
     */
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

    /**
     * Return OS
     *
     * @return integer
     */
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
 
    /**
     * Return composer core package name
     *
     * @return string
     */
    public static function getCorePackageName()
    {
        return "arikaim/core";
    }

    /**
     * Get default output
     *
     * @return string
     */
    public static function getDefaultOutput()
    {
        return (DIRECTORY_SEPARATOR == '\\') ? 'NUL' : '/dev/null';
    }
}
