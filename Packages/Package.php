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
    const EXTENSION = 'extension';
    const TEMPLATE  = 'template';
    const MODULE    = 'module';
    const LIBRARY   = 'library';

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
     * Constructor
     *
     * @param CollectionInterface $properties
     */
    public function __construct(CollectionInterface $properties) 
    {
        $properties['version'] = Utils::formatversion($properties->get('version','1.0.0'));
        $this->properties = $properties;
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
     * @return void
     */
    public function getVersion()
    {
        return $this->properties->get('version','1.0.0');
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
        echo $this->properties->get($name,$default);
        exit();
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

    /**
     * Reinstall package
     *
     * @return bool
     */
    public function reInstall()
    {        
        $this->unInstall();
        return $this->install();
    }
}
