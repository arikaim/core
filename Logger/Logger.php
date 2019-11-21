<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\Logger;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;

use Arikaim\Core\Utils\File;
use Arikaim\Core\Logger\JsonLogsFormatter;
use Arikaim\Core\Logger\LogsProcessor;
use Arikaim\Core\App\Path;

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
    private $fileName;

    /**
     * Constructor
     *
     * @param boolean $enabled
     * @param string $fileName
     */
    public function __construct($enabled = false, $fileName = null) 
    {         
        $fileName = (empty($fileName) == true) ? "errors.log" : $fileName;
        $this->fileName = Path::LOGS_PATH . "errors.log"; 
        $this->enabled = $enabled;

        $this->init();
    }

    /**
     * Create logger
     *
     * @return void
     */
    protected function init()
    {
        // init
        $this->logger = new MonologLogger('system');            
        $handler = new StreamHandler($this->fileName, MonologLogger::DEBUG);
        $jsonFormat = new JsonLogsFormatter();            
        $handler->setFormatter($jsonFormat); 

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
        return (File::exists($this->fileName) == false) ? true : File::delete($this->fileName);
    }

    /**
     * Read logs file with paginator
     *
     * @return void
     */
    public function readSystemLogs()
    {       
        $text ="[" . File::read($this->fileName);      
        $text = rtrim($text,",\n");
        $text .="]\n";

        $logs = json_decode($text,true);
      
        return $logs;
    }

    /**
     * Call logger function
     *
     * @param string $name
     * @param mixed $arguments
     * @return boolean
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
     * @return boolean
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
     * @return boolean
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
