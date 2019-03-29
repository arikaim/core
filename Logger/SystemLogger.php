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

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LogLevel;

use Arikaim\Core\Db\Paginator;
use Arikaim\Core\FileSystem\File;
use Arikaim\Core\Logger\JsonLogsFormatter;
use Arikaim\Core\Logger\LogsProcessor;
use Arikaim\Core\System\Path;
use Arikaim\Core\Logger\Logger;

/**
 * Logger
 */
class SystemLogger extends Logger
{
    /**
     * Logs file name
     *
     * @var string
     */
    private $logs_file_name;

    /**
     * Constructor
     *
     * @param boolean $enabled
     * @param string $logs_file_name
     */
    public function __construct($enabled = false, $logs_file_name = null) 
    {         
        $logs_file_name = (empty($logs_file_name) == true) ? "errors.log" : $logs_file_name;
        $this->logs_file_name = Path::LOGS_PATH . "errors.log"; 

        // init system logger
        $logger = new MonologLogger('system');            
        $handler = new StreamHandler($this->logs_file_name, Logger::DEBUG);
        $json_format = new JsonLogsFormatter();            
        $handler->setFormatter($json_format); 

        $proccesssor = new LogsProcessor();
        $logger->pushHandler($handler);
        $logger->pushProcessor($proccesssor);

        parent::__construct($enabled,$logger);
    }

    /**
     * Delete logs file
     *
     * @return bool
     */
    public function deleteSystemLogs()
    {
        return File::delete($this->logs_file_name);
    }

    /**
     * Read logs file with paginator
     *
     * @return void
     */
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

        $start = ($page == 1) ? 0 : ($page * $per_page);          
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
