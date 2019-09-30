<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Utils;

use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Utils\Utils;

/**
 * Facades abstract class
 */
abstract class StaticFacade 
{
    /**
     * Class instance
     *
     * @var object
     */
    static protected $instance;

    /**
     * Return class name used form facade
     *
     * @return string
     */    
    abstract public static function getInstanceClass();

    /**
     * Get service container item name
     *
     * @return string|null
     */
    public static function getContainerItemName()
    {
        return null;
    }
   
    /**
     * Create instance
     *
     * @return object|null
     */
    private static function createInstance()
    {
        $instance_class = static::getInstanceClass();
        $instance = ($instance_class != null) ? Factory::createInstance($instance_class) : null;
        
        return ($instance == null) ? static::getFromContainer() : $instance;
    }

    /**
     * Get item from container
     * 
     * @return string|null
     */
    public static function getFromContainer()
    {
        // get from service container
        $name = static::getContainerItemName();
        return (Arikaim::getContainer()->has($name) == true) ? Arikaim::getContainer()->get($name) : null;         
    }

    /**
     * Get instance
     *
     * @return void
     */
    public static function getInstance()
    {
        static::$instance = (is_object(static::$instance) == false) ? static::createInstance() : static::$instance;
        return static::$instance;
    }

    /**
     * Call methods on instance as static
     *
     * @param string $method
     * @param mixed $args
     * @return mixed
     * 
     * @throws RuntimeException
     */
    public static function __callStatic($method, $args)
    {
        $name = static::getContainerItemName();
        $instance = static::getInstance($name);
        if (is_object($instance) == false) {        
            throw new \RuntimeException('Facade instance not set.');
        }
        return Utils::call($instance,$method,$args);
    }
}
