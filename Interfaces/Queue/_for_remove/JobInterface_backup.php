<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Interfaces\Queue;

interface JobInterface
{   
    /**
     * Return unique job id
     *
     * @return string
     */ 
    public function getId();

    /**
     * Return job priority
     *
     * @return number
    */
    public function getPriority();

    /**
     * Return job name
     *
     * @return string
     */
    public function getName();

    /**
     * Job code
     *
     * @return void
    */
    public function execute();   
    
  
   
    public function getRecuringInterval();
    public function getExecutionTime();
    public function isDisabled();
    public function isRecuring();
    public function isScheduled();
    public function setExtensionName($name);
    public function getExtensionName();
    public function getJobCommand();
    public function setJobCommand($command);
    public function setScheduleTime($time);
    public function getScheduleTime();
}
