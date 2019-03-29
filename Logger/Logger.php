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
use Psr\Log\LogLevel;

/**
 * Site stats logger
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
     * Enable/Disable stats
     *
     * @var bool
     */
    protected $enabled;

    /**
     * Constructor
     *
     * @param boolean $enabled
     * @param Monolog\Logger $logger
     */
    public function __construct($enabled = true,\Monolog\Logger $logger) 
    {      
        $this->enabled = $enabled;
        $this->logger = $logger;
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

        return ($this->enabled == false) ? $this->logger->{$name}($message,$context) : false;          
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
        return ($this->enabled == false) ? $this->logger->log($level,$message,$context) : false;        
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
    public function setLogger(Monolog\Logger $logger)
    {
        return $this->logger = $logger;
    }
}
