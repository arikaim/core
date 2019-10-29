<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Packages\Drivers;

use Arikaim\Core\Interfaces\Packages\RepositoryDriverInterface;
use Arikaim\Core\Arikaim;

/**
 * Repository driver base class
*/
abstract class RepositoryDriver implements RepositoryDriverInterface
{
    /**
     * Repository url
     *
     * @var string
     */
    protected $repositoryUrl;

    /**
     * Package name
     *
     * @var string
     */
    protected $packageName;

    /**
     * Constructor
     * 
     * @param string $repositoryUrl  
     */
    public function __construct($repositoryUrl)
    {
        $this->repositoryUrl = $repositoryUrl;            
    }

    /**
     * Download package
     *
     * @return bool
     */
    public abstract function download($version = null);
    
    /**
     * Get package last version
     *
     * @return string
     */
    public abstract function getLastVersion();


    /**
     * Get package file name
     *
     * @param string $version
     * @return string
     */
    public function getPackageFileName($version)
    {
        $packageName = $this->getPackageName();
        $fileName = str_replace('/','_',$packageName);
        
        return Arikaim::storage()->getStoragePath('repository/' . $fileName . '-' . $version . '.zip');
    }
    
    /**
     * Get package name
     *
     * @return string
     */
    public function getPackageName()
    {
        return $this->packageName;
    }
}
