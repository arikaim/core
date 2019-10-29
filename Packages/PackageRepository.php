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

use Arikaim\Core\Interfaces\Packages\RepositoryInterface;
use Arikaim\Core\Interfaces\Packages\RepositoryDriverInterface;
use Arikaim\Core\Packages\Drivers\GitHubRepositoryDriver;

/**
 * Package repository base class
*/
abstract class PackageRepository implements RepositoryInterface
{
    /**
     *  Repository type
     */
    const REPOSITORY_TYPE_GITHUB    = 'github';
    const REPOSITORY_TYPE_ARIKAIM   = 'arikaim';
    const REPOSITORY_TYPE_BITBUCKET = 'bitbucket';
    const REPOSITORY_TYPE_COMPOSER  = 'composer';

    /**
     * Repository type
     *
     * @var string|null
     */
    protected $type;

    /**
     * Repository url or name
     *
     * @var string
     */
    protected $repositoryUrl;

    /**
     * Repository driver
     *
     * @var RepositoryDriverInterface
     */
    protected $driver;

    /**
     * Install package
     *
     * @param string|null $version
     * @return boolean
     */
    abstract public function install($version = null);

    /**
     * Constructor
     * 
     * @param string $url  
     */
    public function __construct($repositoryUrl)
    {
        $this->repositoryUrl = $repositoryUrl;
        $this->type = $this->resolveRepositoryUrl();
        $this->driver = $this->createDriver();
    }

    /**
     * Get repository driver
     *
     * @return RepositoryDriverInterface
     */
    public function getRepositoryDriver()
    {
        return $this->driver;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get repository url
     *
     * @return string
     */
    public function getRepositoryUrl()
    {
        return $this->repositoryUrl;
    }

    /**
     * Get package last version
     *
     * @return string
     */
    public function getLastVersion()
    {
        return (is_object($this->driver) == true) ? $this->driver->getLastVersion() : '';
    }

    /**
     * Get package name
     *
     * @return string
     */
    public function getPackageName()
    {
        return (is_object($this->driver) == true) ? $this->driver->getPackageName() : null;
    }

    /**
     * Create repository driver
     *
     * @return void
     */
    private function createDriver()
    {
        switch ($this->type) {
            case Self::REPOSITORY_TYPE_ARIKAIM:
                return new ArikaimRepositoryDriver();
            case Self::REPOSITORY_TYPE_GITHUB:           
                return new GitHubRepositoryDriver($this->repositoryUrl);
        }

        return null;
    }

    /**
     * Resolve package repository type
     *   
     * @return string|null
     */
    private function resolveRepositoryUrl()
    {
        if (empty($this->repositoryUrl) == true) {
            return null;
        }
        if ($this->repositoryUrl == 'arikaim') {
            return Self::REPOSITORY_TYPE_ARIKAIM;
        }
        if (substr($this->repositoryUrl,0,8) == 'composer') {
            return Self::REPOSITORY_TYPE_COMPOSER;
        }
        $url = parse_url($this->repositoryUrl);

        if ($url['host'] == 'github.com' || $url['host'] == 'www.github.com') {
            return Self::REPOSITORY_TYPE_GITHUB;
        }

        if ($url['host'] == 'bitbucket.org' || $url['host'] == 'www.bitbucket.org') {
            return Self::REPOSITORY_TYPE_BITBUCKET;
        }

        return null;       
    }   
}
