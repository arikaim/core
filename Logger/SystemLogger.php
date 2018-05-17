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
use Psr\Log\LogLevel;

use Arikaim\Core\Db\Paginator;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Logger\DbHandler;
use Arikaim\Core\FileSystem\File;
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
            $this->system_log->pushHandler($handler);
            $this->system_log->pushProcessor($proccesssor);
        }
    }

    public static function getLogsPath() 
    {
        return ARIKAIM_ROOT_PATH . join(DIRECTORY_SEPARATOR, array(ARIKAIM_BASE_PATH,'arikaim','logs')) . DIRECTORY_SEPARATOR;
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

    public function readSystemLogs()
    {       
        $logs_text ="[";
        $logs_text .= File::read($this->logs_file_name);
        $logs_text = rtrim($logs_text,",\n");
        $logs_text .="]\n";
        $logs = json_decode($logs_text,true);
      
        // init
        $per_page = Paginator::getRowsPerPage();
        $page = Paginator::getCurrentPage();
        $result['rows'] = [];
        $total_rows = count($logs);

        if ($page == 1) {
            $start = 0;
        } else {
            $start = $page * $per_page;
        }
        $end = $start + $per_page;
       
        if ($total_rows < $end) {
            $end = $total_rows;
        }
        $last_page = floor($total_rows / $per_page);

        for($index = $start; $index < $end; $index++) {
            $row = $logs[$index];
            array_push($result['rows'],$row);
        }

        $result['file'] = $this->logs_file_name;
        $result['paginator']['per_page'] = $per_page;
        $result['paginator']['total'] = $total_rows;
        $result['paginator']['current_page'] = $page;
        $result['paginator']['last_page'] = $last_page;
        $result['paginator']['prev_page'] = Paginator::getPrevPage();
        $result['paginator']['next_page'] = Paginator::getNextPage($last_page);
        $result['paginator']['from'] = 0;
        $result['paginator']['to'] = 0;
        return $result;
    }
}
