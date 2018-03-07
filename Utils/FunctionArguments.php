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

class FunctionArguments 
{   
    const BOOLEAN_TYPE = "boolean";
    const INTEGER_TYPE = "integer";
    const DOUBLE_TYPE = "double";
    const STRING_TYPE = "string";
    const ARRAY_TYPE = "array";
    const OBJECT_TYPE = "object";
    const NULL_TYPE = "NULL";
    const UNKNOWN_TYPE = "unknown type";
   
    public static function getArgument(array $args, $index, $type = null)
    {       
        if ((is_array($args) == false) || (isset($args[$index]) == false)) {
            return null;
        }
        
        $variable_type = gettype($args[$index]);
        if (($type != null) && ($variable_type != $type)) {
            return null;
        }
        return $args[$index];
    }
}
