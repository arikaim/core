<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Packages;

use Arikaim\Core\Interfaces\Packages\PackageManagerInterface;
use Arikaim\Core\FileSystem\File;
use Arikaim\Core\Collection\Collection;
use Arikaim\Core\Arikaim;

/**
 * Package managers base class
*/
abstract class PackageManager implements PackageManagerInterface
{
    /**
     * Path to packages
     *
     * @var string
     */
    protected $path;
    
    /**
     * Package properites file name
     *
     * @var string
     */
    protected $propertiesFileName;

    /**
     * Create package 
     *
     * @param string $name
     * @return PackageInterface
     */
    abstract public function createPackage($name);

    /**
     * Return packages list
     *
     * @param boolean $cached
     * @param mixed $filter
     * @return array
     */
    abstract public function getPackages($cached = false, $filter = null);

    /**
     * Constructor
     *
     * @param string $path
     * @param string $propertiesFileName
     */
    public function __construct($path, $propertiesFileName = 'package.json')
    {
        if (File::exists($path) == false) {
            throw new Exception("Package path ( $path ) not exist!");
            return null;
        }
        $this->path = $path;
        $this->propertiesFileName = $propertiesFileName;
    }

    /**
     * Return packages path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Gte properties file name
     *
     * @return string
     */
    public function getPropertiesFileName()
    {
        return $this->propertiesFileName;
    }

    /**
     * Load package properties file 
     *
     * @param string $name
     * @return Collection
     */
    public function loadPackageProperties($name) 
    {         
        $fileName = $this->getPath() . $name . DIRECTORY_SEPARATOR . $this->getPropertiesFileName();
        $data = File::readJSONFile($fileName);
        $data = (is_array($data) == true) ? $data : [];

        $properties = new Collection($data);    
        if (empty($properties->name) == true) {
            $properties->set('name',$name);
        }           

        return $properties;
    }

    /**
     * Explore packages root directory
     *
     * @param mixed $filter
     * @return array
     */
    protected function scan($filter = null)
    {
        $items = [];
        foreach (new \DirectoryIterator($this->path) as $file) {
            if ($file->isDot() == true || $file->isDir() == false || substr($file->getFilename(),0,1) == '.') {
                continue;
            }
            $name = $file->getFilename();
            if (is_array($filter) == true) {
                $package = $this->createPackage($name);
                $properties = $package->getProperties();                
                foreach ($filter as $key => $value) {                
                    if ($properties->get($key) == $value) {
                        array_push($items,$name);   
                    }
                }
            } else {
                array_push($items,$name);        
            }
        }  
        return $items;
    }

    /**
     * Get package properties
     *
     * @param string $name
     * @param boolean $full
     * @return Collection
     */
    public function getPackageProperties($name, $full = false)
    {
        $package = $this->createPackage($name);

        return $package->getProperties($full);
    }

    /**
     * Find package
     *
     * @param string $param
     * @param mixed $value
     * @return PackageInterface|false
     */
    public function findPackage($param, $value)
    {
        $packages = $this->getPackages();
        foreach ($packages as $name) {
            $properties = $this->loadPackageProperties($name);
            if ($properties->get($param) == $value) {
                return $this->createPackage($name);
            }
        }

        return false;
    }

    /**
     * Instaall all packages
     *
     * @return bool
     */
    public function installAllPackages()
    {
        $errors = 0;
        $packages = $this->getPackages();
        foreach ($packages as $name) {
            $errors += ($this->installPackage($name) == false) ? 1 : 0;
        }

        return ($errors == 0);
    }

    /**
     * Install package
     *
     * @param string $name
     * @return bool
     */
    public function installPackage($name)
    {
        $package = $this->createPackage($name);

        return $package->install();
    }

    /**
     * Uninstall package
     *
     * @param string $name
     * @return bool
     */
    public function unInstallPackage($name)
    {
        $package = $this->createPackage($name);

        return $package->unInstall();
    }

    /**
     * Enable package
     *
     * @param string $name
     * @return bool
     */
    public function enablePackage($name)
    {
        $package = $this->createPackage($name);

        return $package->enable();
    }

    /**
     * Disable package
     *
     * @param string $name
     * @return bool
     */
    public function disablePackage($name)
    {
        $package = $this->createPackage($name);

        return $package->disable();
    }

    /**
     * Reinstall package
     *
     * @param string $name
     * @return bool
     */
    public function reInstallPackage($name)
    {
        $package = $this->createPackage($name);
        
        return $package->reInstall();
    }

    /**
     * Get installed packages.
     *
     * @param integer|null $status
     * @param string|integer $type
     * @return array
     */
    public function getInstalled($status = null, $type = null)
    {
        return [];
    }
}
