<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Packages;

use Arikaim\Core\Interfaces\Packages\PackageManagerInterface;
use Arikaim\Core\FileSystem\File;
use Arikaim\Core\Utils\Properties;

/**
 * Package managers base class
*/
abstract class PackageManager implements PackageManagerInterface
{
    protected $path;
    protected $properties_file_name;

    /**
     * Create package 
     *
     * @param string $name
     * @return PackageInterface
     */
    abstract public function createPackage($name);
    
    abstract public function getPackages($cached = false, $filter = null);

    public function __construct($path, $properties_file_name = 'package.json')
    {
        if (File::exists($path) == false) {
            throw new Exception("Package path ( $path ) not exist!");
            return null;
        }
        $this->path = $path;
        $this->properties_file_name = $properties_file_name;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getPropertiesFileName()
    {
        return $this->properties_file_name;
    }

    public function loadPackageProperties($name) 
    {         
        $full_file_name = $this->getPath() . $name . DIRECTORY_SEPARATOR . $this->getPropertiesFileName();
        $properties = new Properties($full_file_name);    
        if (empty($properties->name) == true) {
            $properties->set('name',$name);
        }             
        return $properties;
    }

    protected function scan($filter = null)
    {
        $items = [];
        foreach (new \DirectoryIterator($this->path) as $file) {
            if ($file->isDot() == true || $file->isDir() == false) continue;
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

    public function getPackageProperties($name, $full = false)
    {
        $package = $this->createPackage($name);
        return $package->getProperties($full);
    }

    public function findPackage($param,$value)
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

    public function installAllPackages()
    {
        $errors = 0;
        $packages = $this->getPackages();
        foreach ($packages as $name) {
            $errors += ($this->installPackage($name) == false) ? 1 : 0;
        }
        return ($errors == 0) ? true : false;
    }

    public function installPackage($name)
    {
        $package = $this->createPackage($name);
        return $package->install();
    }

    public function unInstallPackage($name)
    {
        $package = $this->createPackage($name);
        return $package->unInstall();
    }

    public function enablePackage($name)
    {
        $package = $this->createPackage($name);
        return $package->enable();
    }

    public function disablePackage($name)
    {
        $package = $this->createPackage($name);
        return $package->disable();
    }

    public function reInstallPackage($name)
    {
        $package = $this->createPackage($name);
        return $package->reInstall();
    }

    public function getInstalled($status = null, $type = null)
    {
        return [];
    }
}
