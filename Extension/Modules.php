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

use Arikaim\Container\Container;
use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Packages\ModulePackage;
use Arikaim\Core\Db\Model;

use Psr\Container\ContainerInterface;
use Arikaim\Core\Interfaces\CacheInterface;

/**
 * Modules service locator
 */
class Modules  
{
    /**
     * Container
     * 
     * @var ContainerInterface
    */
    private static $container;

    /**
     * Cache
     *
     * @var CacheInterface
    */
    private $cache;

    /**
     * Constructor
     *
     * @param CacheInterface $cache
     */
    public function __construct(CacheInterface $cache = null)
    {
        $this->cache = $cache;
    } 

    /**
     * Create module instance
     *
     * @param string $name
     * @return ModuleInterface|null
     */
    public function create($name)
    {        
        $module = $this->cache->fetch('module.' . $name);
        if (\is_array($module) == false) {
            $module = Model::Modules()->getPackage($name);
            if ($module != false) {
                $this->cache->save('module.' . $name,$module,3);  
            }  
        } else {
            $module = Model::Modules()->getPackage($name);
        }
        
        return ($module == false) ? null : Factory::createModule($name,$module['class']);
    }

    /**
     * Return true if module installed
     *
     * @param string $name
     * @return boolean
     */
    public function hasModule($name)
    {
        $module = $this->cache->fetch('module.' . $name);
        if (\is_array($module) == true) {
            return true;
        }
        $module = Model::Modules()->getPackage($name);
        
        return \is_array($module);
    }

    /**
     * Check item exists in container
     *
     * @param string $name Item name.
     * @return boolean
    */
    public static function has($name)
    {
        return Self::$container->has($name);
    }

    /**
     * Return service container object.
     *
     * @return ContainerInterface
    */
    public static function getContainer()
    {
        return Self::$container;
    }

    /**
     * Set container
     *
     * @param ContainerInterface $container
     * @return void
     */
    public static function setContainer($container)
    {
        Self::$container = $container;
    }

    /**
     * Add module services in container
     *
     * @return void
     */
    public static function init(CacheInterface $cache)
    {
        Self::$container = new Container();

        $modules = $cache->fetch('services.list');
        if (\is_array($modules) == false) {
            $modules = Model::Modules()->getPackagesList([
                'type'   => ModulePackage::getTypeId('service'), 
                'status' => 1
            ]);
            $cache->save('services.list',$modules,2);    
        } 
        
        foreach ($modules as $module) {
            $serviceName = $module['service_name'];
            if (empty($serviceName) == false) {
                // add to container
                $container[$serviceName] = function() use($module) {
                    return Factory::createModule($module['name'],$module['class']);
                };
            }
            if ($module['bootable'] == true) {
                $service = $container->get($serviceName);
                if (\is_object($service) == true) {
                    $service->boot();
                }
            }           
        }      
    }
}
