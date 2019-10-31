<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
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
     * Repo name
     *
     * @var string
     */
    protected $repositoryName;

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
        $fileName = str_replace('/','_',$this->getPackageName());

        return $fileName . '-' . $version . '.zip';
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

    /**
     * Get repository name
     *
     * @return string
     */
    public function getRepositoryName()
    {
        return $this->repositoryName;
    }
}
