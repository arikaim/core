<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
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
}
