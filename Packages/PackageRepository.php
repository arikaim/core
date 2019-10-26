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
use Arikaim\Core\Utils\Url;

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
    protected $url;

    /**
     * Access token for remote package repository
     *
     * @var string
     */
    protected $access_token;

    /**
     * Repository driver
     *
     * @var RepositoryDriverInterface
     */
    protected $driver;

    /**
     * Download package
     *   
     * @return bool
     */
    abstract public function download();

    /**
     * Get package last version
     *
     * @param string $name
     * @return string|false
     */
    abstract public function getLastVersion();

    /**
     * Install package
     *
     * @return boolean
     */
    abstract public function install();

    /**
     * Constructor
     * 
     * @param string $url  
     */
    public function __construct($url)
    {
        $this->url = $url;
        $this->type = $this->resolveType($url);
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
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Create repository driver
     *
     * @return void
     */
    protected function createDriver()
    {
        switch ($this->type) {
            case Self::REPOSITORY_TYPE_ARIKAIM:
                return new ArikaimRepositoryDriver();
            case Self::REPOSITORY_TYPE_GITHUB:
                return new GitHubRepositoryDriver();
        }

        return nulll;
    }

    /**
     * Resolve package repository type
     *
     * @param string $repository
     * @return string|null
     */
    protected function resolveType($repository)
    {
        if ($repository == 'arikaim') {
            return Self::REPOSITORY_TYPE_ARIKAIM;
        }
        if (substr($repository,0,8) == 'composer') {
            return Self::REPOSITORY_TYPE_COMPOSER;
        }
        $url = parse_url($repository);

        if ($url['host'] == 'github.com' || $url['host'] == 'www.github.com') {
            return Self::REPOSITORY_TYPE_GITHUB;
        }

        if ($url['host'] == 'bitbucket.org' || $url['host'] == 'www.bitbucket.org') {
            return Self::REPOSITORY_TYPE_BITBUCKET;
        }

        return null;       
    }   
}
