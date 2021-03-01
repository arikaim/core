<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\Extension;

use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Interfaces\CacheInterface;
use Arikaim\Core\Interfaces\ModuleInterface;

/**
 * Modules service locator
 */
class Modules  
{
    /**
     * Cache
     *
     * @var CacheInterface|null
    */
    private $cache;

    /**
     * Constructor
     *
     * @param CacheInterface $cache
     */
    public function __construct(?CacheInterface $cache = null)
    {
        $this->cache = $cache;
    } 

    /**
     * Create module instance
     *
     * @param string $name
     * @return ModuleInterface|null
     */
    public function create(string $name)
    {        
        $module = (\is_null($this->cache) == false) ? $this->cache->fetch('module.' . $name) : null;
        if (empty($module) == true) {
            $module = Model::Modules()->getPackage($name);
            if ($module !== false && \is_null($this->cache) == false) {
                $this->cache->save('module.' . $name,$module,5);  
            }  
        } 
        
        $obj = ($module === false) ? null : Factory::createModule($name,$module['class']);
        if (($obj instanceof ModuleInterface) == false) {
            return null;
        }

        $obj->setConfig($module['config']);
        $obj->setModuleName($name);
        $obj->boot();
        $instance = $obj->getInstance();

        return (\is_null($instance) == true) ? $obj : $instance;
    }

    /**
     * Return true if module installed
     *
     * @param string $name
     * @return boolean
     */
    public function hasModule(string $name): bool
    {
        $module = $this->cache->fetch('module.' . $name);
        if (\is_array($module) == true) {
            return true;
        }
        $module = Model::Modules()->getPackage($name);
        
        return (\is_array($module) == true);
    }

    /**
     * Load module config
     *
     * @param string $name
     * @return bool
     */
    protected function loadConfig(string $name): bool
    {
        $model = Model::Modules()->findByColumn($name,'name');
        if (\is_object($model) == true) {
           // $this->setConfig($model->config);
            
            return true;
        } 
        
        return false;
    }
}
