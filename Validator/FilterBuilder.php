<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Validator;

use Arikaim\Core\Utils\Factory;

/**
 * Filter factory class
 */
class FilterBuilder
{
    public function __construct() 
    {
    }

    public static function createFilter($class_name, $args = null)
    {              
        return Factory::createInstance(Factory::getValidatorFiltersClass($class_name),$args);             
    }

    public static function __callStatic($name, $args)
    {  
        return Self::createFilter(ucfirst($name),$args);       
    }

    public function __call($name, $args)
    {  
        return Self::createFilter(ucfirst($name),$args);       
    }

    
}
