<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Form;

use Arikaim\Core\Utils\Factory;

/**
 * Filter factory class
 */
class Filter
{
    public function __construct() 
    {
    }

    public static function createFilter($class_name, $args = null)
    {              
        return Factory::createInstance(Self::getFormFiltersNamespace() . $class_name,$args);             
    }

    public static function __callStatic($name, $args)
    {  
        return Self::createFilter(ucfirst($name),$args);       
    }

    public function __call($name, $args)
    {  
        return Self::createFilter(ucfirst($name),$args);       
    }

    public static function getFormFiltersNamespace()
    {
        return "Arikaim\\Core\\Form\\Filter\\"; 
    }
}
