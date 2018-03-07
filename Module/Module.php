<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Module;

use Arikaim\Core\Interfaces\ModuleInterface;

class Module implements ModuleInterface
{
    protected $service_name;
    protected $title;
    protected $description;
    protected $version;
    protected $bootable;

    public function __construct()
    {
    }

    public function getServiceName()
    {
        return $this->service_name;
    }

    public function getModuleTitle()
    {
        return $this->title;
    }

    public function getModuleDescription()
    {
        return $this->description;
    }

    public function setServiceName($name)
    {
        return $this->service_name = $name;
    }

    public function setModuleTitle($title)
    {
        return $this->title = $title;
    }

    public function setModuleDescription($description)
    {
        return $this->description = $description;
    }

    public function getModuleVersion()
    {
        return $this->version;
    }

    public function setModuleVersion($version)
    {
        $this->version = $version;
    }

    public function isBootable()
    {
        return ($this->bootable == true) ? true : false; 
    }

    public function setBootable($bootable = true)
    {
        $this->bootable = $bootable;
    }
}
