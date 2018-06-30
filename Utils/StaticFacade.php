<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Utils;

use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Arikaim;

abstract class StaticFacade 
{
    static private $instance = null;

    public static function getInstanceClass()
    {
        return null;
    }

    public static function getContainerItemName()
    {
        return null;
    }

    private static function createInstance()
    {
        $instance_class = static::getInstanceClass();
        if ($instance_class != null) {
            $instance = Factory::createInstance($instance_class);
            return $instance;
        }
        // get from service container
        $container_item_name = static::getContainerItemName();
        if (Arikaim::getContainer()->has($container_item_name) == true) {
            $instance = Arikaim::getContainer()->get($container_item_name);
            return $instance;
        }
        return null;
    }

    public static function getInstance()
    {
        if (is_object(static::$instance) == false) {
            static::$instance = static::createInstance();
        }
        return static::$instance;
    }

    public static function __callStatic($method, $args)
    {
        $instance = static::getInstance();
        if (is_object($instance) == false) {        
            throw new RuntimeException('Facade instance not set.');
        }
        return $instance->$method(...$args);
    }
}
