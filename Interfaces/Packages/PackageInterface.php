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
 * Package interface, all packages type should impelement it.
 */
interface PackageInterface 
{  
    /**
     * Return package name
     *
     * @return string
     */
    public function getName();

    /**
     * Return package properties
     *
     * @param boolean $full
     * @return Collection
     */
    public function getProperties($full = false);

    /**
     * Validate package properties 
     *
     * @return bool
     */
    public function validate();

    /**
     * Install package
     *
     * @return bool
     */
    public function install();

    /**
     * Unintsll package
     *
     * @return bool
     */
    public function unInstall();

    /**
     * Reinstall package
     *
     * @return void
     */
    public function reInstall();

    /**
     * Enable package
     *
     * @return bool
     */
    public function enable();

    /**
     * Disable package
     *
     * @return bool
     */
    public function disable();

    /**
     * Get package repository
     *
     * @return RepositoryInterface
     */
    public function getRepository();
}
