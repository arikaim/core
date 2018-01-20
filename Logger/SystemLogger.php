<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Logger;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\JsonFormatter;

use Psr\Log\LogLevel;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Logger\DbHandler;
use Arikaim\Core\Utils\File;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Logger\JsonLogsFormatter;
use Arikaim\Core\Logger\SystemLogsProcessor;

class SystemLogger
{
    private $stats;
    private $system_log;
    private $logs_file_name;

    public function __construct() 
    {      
        $this->logs_file_name = SystemLogger::getLogsPath() . "errors.log";        
        // init site stats logger   
        if (Arikaim::options('logger.stats') == true) {               
            $handler = new DbHandler(Logger::DEBUG);
            $this->stats = new Logger('stats');
            $this->stats->pushHandler($handler);
        }
        // init system logger
        if (Arikaim::options('logger') == true) {
            $this->system_log = new Logger('system');            
            $handler = new StreamHandler($this->logs_file_name, Logger::DEBUG);
            $json_format = new JsonLogsFormatter();            
            $handler->setFormatter($json_format); 

            $proccesssor = new SystemLogsProcessor();
            // init
            $this->system_log->pushHandler($handler);
            $this->system_log->pushProcessor($proccesssor);
        }
    }

    public static function getLogsPath() 
    {
        return Arikaim::getRootPath() . join(DIRECTORY_SEPARATOR, array(Arikaim::getBasePath(),'arikaim','logs')) . DIRECTORY_SEPARATOR;
    }

    public function addStats($message, $context = [], $level = LogLevel::INFO) 
    {        
        if (is_object($this->stats) == false) {
            return false;
        }
        $this->stats->log($level,$message,$context);
    }

    public function __call($name, $arguments)
    {           
        $message = $arguments[0];
        $context = isset($arguments[1]) ? $arguments[1] : [];
        if (is_object($this->system_log) == true) {
            return $this->system_log->{$name}($message,$context);
        }
        return false;
    }
    
    public function log($level, $message, array $context = [])
    {   
        if (is_object($this->system_log) == true) {
            return $this->system_log->log($level,$message,$context);
        }
        return false;
    }

    public function deleteSystemLogs()
    {
        return File::delete($this->logs_file_name);
    }

    public function readSystemLogs($page = 1,$per_page = 50)
    {       
        $logs_text ="[";
        $logs_text .= File::load($this->logs_file_name);
        $logs_text = rtrim($logs_text,",\n");
        $logs_text .="]\n";
        $logs = json_decode($logs_text,true);
      
        // init
        $result['rows'] = [];
        $start = $page * $per_page;
        $end = $start + $per_page;
        if (count($logs) < $end) {
            $end = count($logs);
        }
        
        for($index = $start; $index < $end; $index++) {
            $row = $logs[$index];
            array_push($result['rows'],$row);
        }

        $result['file'] = $this->logs_file_name;
        $result['paginator']['per_page'] = $per_page;
        return $result;
    }
}
