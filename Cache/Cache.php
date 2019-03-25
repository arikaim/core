<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Cache;

use Doctrine\Common\Cache\FilesystemCache;
use Arikaim\Core\FileSystem\File;
use Arikaim\Core\Arikaim;

/**
 * Cache 
*/
class Cache 
{
    protected $driver;
    
    private $disabled;

    public function __construct($driver = null, $disabled = false)
    {
        $this->disabled = (bool)$disabled;
        if (empty($driver) == false) {
            $this->setDriver($driver);
        } else {
            $this->driver = new FilesystemCache(ARIKAIM_CACHE_PATH);
        }
    }

    public function setDriver($driver)
    {
        if ($driver instanceof Doctrine\Common\Cache\Cache) {
            $this->driver = $driver;
        }
        throw new Exception("Error Cache driver not valid!", 1);
    }

    public function fetch($id)
    {
        return ($this->disabled == true) ? null : $this->driver->fetch($id);
    }
   
    public function contains($id)
    {
        return $this->driver->contains($id);
    }

    /**
     * Undocumented function
     *
     * @param string $id item id
     * @param mixed $data item data
     * @param integer $life_time  lifetime in minutes
     * @return bool
     */
    public function save($id, $data, $life_time = 0)
    {
        return ($this->disabled == true) ? false : $this->driver->save($id,$data,($life_time * 60));
    }

    public function delete($id)
    {
        if ($this->driver->contains($id) == true) {
            return $this->driver->delete($id);
        }
        return true;
    }

    public function getStats()
    {
        return $this->driver->getStats();
    }

    public function deleteAll()
    {
        return $this->driver->deleteAll();
    }

    public function clear()
    {
        $this->driver->deleteAll();
        return File::deleteDirectory(ARIKAIM_CACHE_PATH);
    }

    public function hasRouteCache()
    {
        return File::exists(Arikaim::settings('routerCacheFile'));
    }

    public function clearRouteCache()
    {
        $this->delete('routes.list');
        return File::delete(Arikaim::settings('routerCacheFile'));
    }
    
    public function deleteModuleItems()
    {
        $this->delete('services.list');
        $this->delete('middleware.list');
    }

    public function deleteTemplateItems()
    {
        $this->delete('template.files');
        $this->delete('templates.list');
        $this->delete('routes.list');
        $this->delete('template.theme.file');
    }

    public function deleteExtensionItems()
    {
        $this->delete('routes.list');
        $this->delete('extensions.list');
    }
}
