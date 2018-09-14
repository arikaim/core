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

    public function boot()
    {        
    }
    
    public function getServiceName()
    {
        return $this->service_name;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setServiceName($name)
    {
        return $this->service_name = $name;
    }

    public function setTitle($title)
    {
        return $this->title = $title;
    }

    public function setDescription($description)
    {
        return $this->description = $description;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion($version)
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
