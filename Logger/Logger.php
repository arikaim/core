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

use Arikaim\Core\FileSystem\File;
use Arikaim\Core\Logger\JsonLogsFormatter;
use Arikaim\Core\Logger\LogsProcessor;
use Arikaim\Core\System\Path;

/**
 * Logger
 */
class Logger
{
    /**
     * Logger object
     *
     * @var Monolog\Logger
     */
    protected $logger;
    
    /**
     * Enable/Disable logger
     *
     * @var bool
     */
    protected $enabled;

    /**
     * Logs file name
     *
     * @var string
     */
    private $file_name;

    /**
     * Constructor
     *
     * @param boolean $enabled
     * @param string $file_name
     */
    public function __construct($enabled = false, $file_name = null) 
    {         
        $file_name = (empty($file_name) == true) ? "errors.log" : $file_name;
        $this->file_name = Path::LOGS_PATH . "errors.log"; 
        $this->enabled = $enabled;

        $this->init();
    }

    protected function init()
    {
        // init
        $this->logger = new MonologLogger('system');            
        $handler = new StreamHandler($this->file_name, MonologLogger::DEBUG);
        $json_format = new JsonLogsFormatter();            
        $handler->setFormatter($json_format); 

        $proccesssor = new LogsProcessor();
        $this->logger->pushHandler($handler);
        $this->logger->pushProcessor($proccesssor);   
    }

    /**
     * Delete logs file
     *
     * @return bool
     */
    public function deleteSystemLogs()
    {
        return (File::exists($this->file_name) == false) ? true : File::delete($this->file_name);
    }

    /**
     * Read logs file with paginator
     *
     * @return void
     */
    public function readSystemLogs()
    {       
        $logs_text ="[";
        $logs_text .= File::read($this->file_name);
        $logs_text = rtrim($logs_text,",\n");
        $logs_text .="]\n";
        $logs = json_decode($logs_text,true);
      
        return $logs;
    }

    /**
     * Call logger function
     *
     * @param string $name
     * @param mixed $arguments
     * @return void
     */
    public function __call($name, $arguments)
    {           
        $message = $arguments[0];
        $context = isset($arguments[1]) ? $arguments[1] : [];

        return ($this->enabled == true) ? $this->logger->{$name}($message,$context) : false;          
    }
    
    /**
     * Add log record
     *
     * @param string $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log($level, $message, array $context = [])
    {   
        return ($this->enabled == true) ? $this->logger->log($level,$message,$context) : false;        
    } 

    /**
     * Add error log
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function error($message,$context = [])
    {      
        return ($this->enabled == true) ? $this->logger->error($message,$context) : false;      
    }

    /**
     * Return stats logger 
     *
     * @return Monolog\Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Set logger
     *
     * @param Monolog\Logger $logger
     * @return void
     */
    public function setLogger($logger)
    {
        return $this->logger = $logger;
    }
}
