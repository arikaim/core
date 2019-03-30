<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Interfaces\Packages;

use Arikaim\Core\Interfaces\Packages\PackageInterface;

interface PackageManagerInterface 
{  
    /**
     * Create package obj
     *
     * @param string $name
     * @return PackageInterface
     */
    public function createPackage($name);

    /**
     * Find package 
     */
    public function findPackage($param,$value);

    /**
     *  @return Collection
     */
    public function getPackageProperties($name, $full = true);
    
    /**
     * Install package
     *
     * @param string $name
     * @return bool
     */
    public function installPackage($name);

    /**
     * Uninstall package
     *
     * @param string $name
     * @return bool
     */
    public function unInstallPackage($name);
    public function reInstallPackage($name);
    public function enablePackage($name);
    public function disablePackage($name);
    
    /**
     *  Install all packages 
     *  @return bool
     */
    public function installAllPackages();

    /**
     * Return package list (cached)
     * If cached list is empty run sacen and save new values to cache
     * @param bool cached
     * @param array filter
     * @return array
     */
    public function getPackages($cached = false, $filter = null);

    /**
     * Return installed packages
     *
     * @param mixed|null $status
     * @param mixed|null $type
     * @return array
     */
    public function getInstalled($status = null, $type = null);
}