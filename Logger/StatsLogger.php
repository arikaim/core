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

use Arikaim\Core\Logger\Logger;
use Arikaim\Core\Logger\DbHandler;

/**
 * Site stats logger
 */
class StatsLogger extends Logger
{ 
    /**
     * Constructor
     *
     * @param boolean $enabled
     */
    public function __construct($enabled = false) 
    {      
        // init site stats logger   
        $handler = new DbHandler(MonologLogger::DEBUG);
        $logger = new MonologLogger('stats');
        $logger->pushHandler($handler);

        parent::__construct($enabled,$logger);
    }

    /**
     * Add stats record
     *
     * @param string $message
     * @param array $context
     * @param string $level
     * @return void
     */
    public function addStats($message, $context = [], $level = LogLevel::INFO) 
    {        
        return ($this->enabled == false) ? $this->logger->log($level,$message,$context) : false;          
    }
}
