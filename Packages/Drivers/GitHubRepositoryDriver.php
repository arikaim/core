<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Packages\Drivers;

use Arikaim\Core\Packages\Interfaces\RepositoryDriverInterface;
use Arikaim\Core\Packages\Drivers\RepositoryDriver;
use Arikaim\Core\App\Url;
use Arikaim\Core\App\Path;
use Arikaim\Core\Arikaim;

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
      
        $packageFileName = Path::STORAGE_REPOSITORY_PATH . $this->getPackageFileName($version); 
        Arikaim::storage()->delete('repository/' . $this->getPackageFileName($version));
        try {
            Arikaim::http()->get($url,['sink' => $packageFileName]);
        } catch (\Exception $e) {
            return false;
        }
      
        return Arikaim::storage()->has('repository/' . $this->getPackageFileName($version));
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
            $url = "http://api.github.com/repos/" . $packageName . "/releases/latest";
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
     * Resolve package name and repository name
     *
     * @return void
     */
    private function resolvePackageName()
    {
        $url = parse_url($this->repositoryUrl);
        $path = trim(str_replace('.git','',$url['path']),'/');
        $tokens = explode('/',$path);   

        $this->repositoryName = $tokens[1];    
        $this->packageName = $tokens[0] . '/' .  $this->repositoryName;       
    }
}
