<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Interfaces\Packages;

/**
 * Package repository interface
 */
interface RepositoryInterface 
{  
    /**
     * Get repository url
     *
     * @return string
     */
    public function getRepositoryUrl();
  
    /**
     * Install repository
     *
     * @param string|null $version
     * @return boolean
     */
    public function install($version = null);

    /**
     * Get repository type
     *
     * @return string
     */
    public function getType();

    /**
     * Get package name
     *
     * @return string
     */
    public function getPackageName();

    /**
     * Get repository driver
     *
     * @return RepositoryDriverInterface
     */
    public function getRepositoryDriver();    
}
