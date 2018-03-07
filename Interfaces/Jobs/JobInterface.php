<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Interfaces\Jobs;

interface JobInterface
{    
    public function execute();   
    public function getPriority();
    public function getId();
    public function getName();
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
