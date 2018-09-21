<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\View;

use Arikaim\Core\Utils\DateTime;

class Filters  
{
    public function __construct()
    {
    }

    public function htmlTag($content, $name, $attributes, $single_tag = false, $start_tag_only = false)
    {    
        $attr_list = $this->getAttributes($attributes);
        if ($single_tag == true) {
            return "<$name $attr_list />";
        }
        if ($start_tag_only == true) {
            return "<$name $attr_list>";
        }
        return "<$name $attr_list>$content</$name>";   
    }
        
    public function getAttributes($attributes)
    {        
        if (is_array($attributes) == false) {
            return "";
        }
        $attr_list = "";   
        foreach ($attributes as $key => $value) {
            if ($key == "content") continue;
            if (is_array($value) == true) continue;
            $attr_list .= " " . $this->attr($value,$key);
        }
        return $attr_list;   
    }
    
    public function htmlStartTag($name, $attributes)
    {        
        return $this->htmlTag(null,$name,$attributes,false,true);
    }
    
    public function htmlSingleTag($name, $attributes)
    {        
        return $this->htmlTag(null,$name,$attributes,true);
    }
    
    public function attr($value, $name = null, $default = null)
    {   
        $value = (empty($value) == true) ? $default : $value;
        if (empty($value) == false) {
            return "$name=\"$value\"";  
        }
        return "";
    }

    public function showArray($array)
    {
        return (is_string($array) == true) ? "$array" : print_r((array) $array,true);          
    }

    public function dump($var)
    {
        return var_dump($var);
    }
    
    public function is($value, $equal, $return_value)
    {
        if (is_array($equal) == true) {
            if (in_array($value,$equal) == true) {
                return $return_value;
            }
        }
        if ($value === 'false') $value = false;
        if ($value === 'true')  $value = true;

        if ($value == $equal) {           
            return $return_value;
        }
        return null;
    }

    public function dateFormat($timestamp, $format = null)
    {
        if ($format == null) {
            $format = DateTime::getDateFormat(); 
        }
        return date($format,$timestamp);
    }

    public function timeFormat($timestamp, $format = null)
    {
        if ($format == null) {
            $format = DateTime::getTimeFormat(); 
        }
        return date($format,$timestamp);
    }

    public function getDefaultValue($value, $default)
    {
        if (empty($value) == true || $value == null) {
            return $default;
        }
        return $value;
    }
}
