<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Queue\Jobs;

use Arikaim\Core\Interfaces\Queue\JobInterface;
use Arikaim\Core\Utils\Utils;

abstract class Job implements JobInterface
{
    protected $id;
    protected $name;
    protected $priority;
    protected $extension_name;
  
    public function __construct($extension_name, $name = "", $priority = 0)
    {
        $this->setExtensionName($extension_name);
        $this->setName($name);
        $this->setPriority($priority);
    }

    abstract public function execute();

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getExtensionName()
    {
        return $this->extension_name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    public function setExtensionName($name)
    {
        return $this->extension_name = $name;
    }
}
