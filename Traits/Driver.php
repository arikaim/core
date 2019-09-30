<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Traits;

/**
 * Driver trait
*/
trait Driver
{
    /**
     * Driver name
     *
     * @var string
    */
    protected $driver_name = null;

    /**
     * Driver class
     *
     * @var string|null
     */
    protected $driver_class = null;

    /**
     * Driver version
     *
     * @var string
     */
    protected $driver_version = '1.0.0';

    /**
     * Driver title (display name)
     *
     * @var string
     */
    protected $driver_title = null;

    /**
     * Driver description
     *
     * @var string
     */
    protected $driver_description = null;

    /**
     * Driver category
     *
     * @var string
     */
    protected $driver_category = null;

    /**
     * Driver config
     *
     * @var array
     */
    protected $driver_config;
    
    /**
     * Driver instance
     *
     * @var object|null
     */
    protected $instance;

    /**
     * Return driver name.
     *
     * @return string
     */
    public function getDriverName()
    {
        return $this->driver_name;
    }

    /**
     * Get driver instance
     *
     * @return object
     */
    public function getInstance()
    {
        return (empty($this->instance) == true) ? $this : $this->instance;
    }

    /**
     * Return driver display name.
     *
     * @return string
     */
    public function getDriverTitle()
    {
        return (empty($this->driver_title) == true) ? $this->getDriverName() : $this->driver_title;
    }

    /**
     * Return driver description.
     *
     * @return string
     */
    public function getDriverDescription()
    {
        return $this->driver_description;
    }

    /**
     * Return driver category.
     *
     * @return string
     */
    public function getDriverCategory()
    {
        return $this->driver_category;
    }

    /**
     * Return driver version.
     *
     * @return string
     */
    public function getDriverVersion()
    {
        return $this->driver_version;
    }

    /**
     * Return driver extension name (if driver class is located in extension)
     *
     * @return string
    */
    public function getDriverExtensionName()
    {
        return $this->driver_extension_name;
    }

    /**
     * Get driver class
     *
     * @return string
     */
    public function getDriverClass()
    {
        return (empty($this->driver_class) == true) ? get_class() : $this->driver_class;
    }

    /**
     * Set driver class
     *
     * @param string $class
     * @return void
     */
    public function setDriverClass($class)
    {
        $this->driver_class = $class;
    }

    /**
     * Get driver config
     *
     * @return array
     */
    public function getDriverConfig()
    {
        return (is_array($this->driver_config) == true) ? $this->driver_config : [];
    }

    /**
     * Set driver name, title, category, description , version params
     *
     * @param string $name
     * @param string|null $category
     * @param string|null $title
     * @param string|null $class
     * @param string|null $description
     * @param string|null $version
     * @param string|null $extension_name
     * @return void
     */
    public function setDriverParams($name, $category = null, $title = null, $description = null, $version = null, $extension_name = null, $class = null)
    {
        $this->driver_name = $name;
        $this->driver_category = $category;
        $this->driver_title = $title;
        $this->driver_class = $class;
        $this->driver_description = $description;
        $this->driver_version = (empty($version) == true) ? '1.0.0' : $version;
        $this->driver_extension_name = $extension_name;
    }

    /**
     * Initialize driver
     *
     * @return void
     */
    public function initDriver($properties)
    {     
        $config = $properties->getValues();
        $this->instance = new $this->driver_class($config);   
    }

    /**
     * Build driver config properties
     *
     * @param Arikaim\Core\Collection\Properties $properties;
     * 
     * @return array
     */
    public function createDriverConfig($properties)
    {
    }
}
