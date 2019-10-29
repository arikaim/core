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
use Arikaim\Core\Packages\Drivers\RepositoryDriver;
use Arikaim\Core\System\Url;
use Arikaim\Core\Arikaim;
use Arikaim\Core\FileSystem\File;

/**
 * GitHub repository driver class
*/
class GitHubRepositoryDriver extends RepositoryDriver implements RepositoryDriverInterface
{
    /**
     * Constructor
     * 
     * @param string $repositoryUrl  
     */
    public function __construct($repositoryUrl)
    {
        parent::__construct($repositoryUrl);
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
        $url = "http://github.com/" . $this->getPackageName() . "/archive/" . $version . ".zip";
        // write to storage/repository
        $result = Arikaim::http()->get($url,['sink' => $this->getPackageFileName($version)]);

        return File::exists($this->getPackageFileName($version));
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
