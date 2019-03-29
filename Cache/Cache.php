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
    /**
     * Cache driver
     *
     * @var Doctrine\Common\Cache\Cache
     */
    protected $driver;
    
    /**
     * Cache status
     *
     * @var bool
     */
    private $disabled;

    /**
     * Constructor
     *
     * @param Doctrine\Common\Cache\Cache $driver
     * @param boolean $disabled
     */
    public function __construct($driver = null, $disabled = false)
    {
        $this->disabled = (bool)$disabled;
        if (empty($driver) == false) {
            $this->setDriver($driver);
        } else {
            $this->driver = new FilesystemCache(ARIKAIM_CACHE_PATH);
        }
    }

    /**
     * Return cache driver
     *
     * @return Doctrine\Common\Cache\Cache
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Set cache driver
     *
     * @param Doctrine\Common\Cache\Cache $driver
     * @return void
     */
    public function setDriver($driver)
    {
        if ($driver instanceof Doctrine\Common\Cache\Cache) {
            $this->driver = $driver;
        } else {
            throw new Exception("Error cache driver not valid!", 1);
        }
    }

    /**
     * Read item
     *
     * @param  string $id
     * @return mixed|null
     */
    public function fetch($id)
    {
        return ($this->disabled == true) ? null : $this->driver->fetch($id);
    }
   
    /**
     * Check if cache contains item
     *
     * @param string $id
     * @return bool
     */
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

    /**
     * Deleet cache item
     *
     * @param string $id
     * @return bool
     */
    public function delete($id)
    {
        if ($this->driver->contains($id) == true) {
            return $this->driver->delete($id);
        }
        return true;
    }

    /**
     * Return cache stats
     *
     * @return void
     */
    public function getStats()
    {
        return $this->driver->getStats();
    }

    /**
     * Delete all cache items
     *
     * @return void
     */
    public function deleteAll()
    {
        return $this->driver->deleteAll();
    }

    /**
     * Delete all cache items + views cache files and route cache
     *
     * @return void
     */
    public function clear()
    {
        $this->driver->deleteAll();
        return File::deleteDirectory(ARIKAIM_CACHE_PATH);
    }

    /**
     * Return true if route ceche file exist
     *
     * @return boolean
     */
    public function hasRouteCache()
    {
        return File::exists(Arikaim::settings('routerCacheFile'));
    }

    /**
     * Delete route cache items + route cache file
     *
     * @return bool
     */
    public function clearRouteCache()
    {
        $this->delete('routes.list');
        return File::delete(Arikaim::settings('routerCacheFile'));
    }
    
    /**
     * Delete modules cache items
     *
     * @return void
     */
    public function deleteModuleItems()
    {
        $this->delete('services.list');
        $this->delete('middleware.list');
    }

    /**
     * Delete templates cache items
     *
     * @return void
     */
    public function deleteTemplateItems()
    {
        $this->delete('options');
        $this->delete('template.files');
        $this->delete('templates.list');
        $this->delete('routes.list');
        $this->delete('template.theme.file');
    }

    /**
     * Delete extensions cache items
     *
     * @return void
     */
    public function deleteExtensionItems()
    {
        $this->delete('routes.list');
        $this->delete('extensions.list');
    }
}
