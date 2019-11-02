<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Packages;

use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Interfaces\Packages\PackageInterface;
use Arikaim\Core\Interfaces\Collection\CollectionInterface;

/**
 * Package base class
*/
class Package implements PackageInterface
{
    /**
     * Reference to package repository
     *
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * Package properties
     *
     * @var CollectionInterface
     */
    protected $properties;

    /**
     * Package type
     *
     * @var string
     */
    protected $packageType;

    /**
     * Constructor
     *
     * @param CollectionInterface $properties
     * @param string $packageType
     */
    public function __construct(CollectionInterface $properties, $packageType) 
    {
        $properties['version'] = Utils::formatversion($properties->get('version','1.0.0'));
        $this->properties = $properties;
        $this->packageType = $packageType;
    }

    /**
     * Get package repository
     *
     * @return RepositoryInterface
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Get Package version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->properties->get('version','1.0.0');
    }

    /**
     * Get package type
     *
     * @return string
     */
    public function getType()
    {
        return $this->packageType;
    }

    /**
     * Return package name
     *
     * @return string
     */
    public function getName()
    {
        return $this->properties->get('name');
    }

    /**
     * Return package properties
     *
     * @param boolean $full
     * @return CollectionInterface
     */
    public function getProperties($full = false)
    {
        return $this->properties;
    }

    /**
     * Get package property
     *
     * @param srting $name
     * @param mixed $default
     * @return mixed
     */
    public function getProperty($name, $default = null)
    {
        return $this->properties->get($name,$default);
    }

    /**
     * Validate package properties
     *
     * @return bool
     */
    public function validate()
    {
        return true;
    }

    /**
     * Install package.
     *
     * @return bool
     */
    public function install()   
    {        
        return false;
    }

    /**
     * UnInstall package
     *
     * @return bool
     */
    public function unInstall() 
    {      
        return false;  
    }

    /**
     * Enable package
     *
     * @return bool
     */
    public function enable()    
    {
        return false;
    }

    /**
     * Disable package
     *
     * @return bool
     */
    public function disable()   
    {        
        return false;
    }  
}
