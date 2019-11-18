<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Interfaces\Packages;

/**
 * Repositorydriver interface
 */
interface RepositoryDriverInterface 
{  
    /**
     * Download package
     *
     * @return bool
     */
    public function download($version = null);

    /**
     * Get package last version
     *
     * @return string
     */
    public function getLastVersion();

    /**
     * Get package name
     *
     * @return string
     */
    public function getPackageName();

    /**
     * Get repository name
     *
     * @return string
     */
    public function getRepositoryName();
}
