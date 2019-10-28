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
use Arikaim\Core\System\Url;
use Arikaim\Core\Arikaim;

/**
 * GitHub repository driver class // TODO
*/
class GitHubRepositoryDriver implements RepositoryDriverInterface
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
        $this->resolvePackageName();        
    }

    /**
     * Download package
     *
     * @return bool
     */
    public function download($version = null)
    {
        $version = (empty($version) == true) ? $this->getLastVersion() : $version;
        $url = "https://github.com/" . $this->getPackageName() . "/archive/" . $version . ".zip";
        // write to storage
        Arikkaim::http()->get($url,['sink' => $this->getPackageFileName($version)]);

    }

    /**
     * Get package file name
     *
     * @param string $version
     * @return string
     */
    public function getPackageFileName($version)
    {
        Arikiam::storage()->getStoragePath('/repository/' . $this->packageName . '-' . $version . '.zip');
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
     * Get package last version
     *
     * @return string
     */
    public function getLastVersion()
    {
        $packageName = $this->getPackageName();
        $cached = Arikaim::cache()->fetch($packageName);
        if (empty($cached) == true) {
            $url = "https://api.github.com/repos/" . $packageName . "/releases/latest";
            $json = Url::fetch($url);
            $data = \json_decode($json,true);
            if (is_array($data) == true) {
                $version = (isset($data['tag_name']) == true) ? $data['tag_name'] : '';
                Arikaim::cache()->save($packageName,$version,1);
                return $version;
            }
            return '';
        } 
       
        return $cached;
    }

    /**
     * Resolve package name
     *
     * @return void
     */
    private function resolvePackageName()
    {
        $url = parse_url($this->repositoryUrl);
        $path = trim(str_replace('.git','',$url['path']),'/');
        $tokens = explode('/',$path);       
        $this->packageName = $tokens[0] . '/' . $tokens[1];
    }
}
