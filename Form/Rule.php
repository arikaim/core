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

class Rule
{
    public function __construct() 
    {
    
    }

    public static function createRule($class_name, $args = null)
    {              
        $rule = Factory::createInstance(Self::getFormRulesNamespace() . $class_name,$args);       
        return $rule;   
    }

    public static function __callStatic($name, $args)
    {  
        return Self::createRule(ucfirst($name),$args);       
    }

    public function __call($name, $args)
    {  
        return Self::createRule(ucfirst($name),$args);       
    }

    public static function getFormRulesNamespace()
    {
        return "Arikaim\\Core\\Form\\Rule\\"; 
    }

}
