<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\View\Html;

use Arikaim\Core\Utils\File;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Utils\DateTime;
use Arikaim\Core\Arikaim;

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
        $attr_list = "";
        if (is_array($attributes) == false) return "";
        
        foreach ($attributes as $key => $value) {
            if ($key == "content") continue;
            if ( is_array($value) == true) continue;
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
        $result = "";       
        if (empty($value) == true) {
            $value = $default;           
        }
        $result = "$name=\"$value\"";             
        return $result;
    }

    public function showArray($array)
    {
        if (is_array($array) == false) {
            return "$array";
        }
        return print_r($array,true);
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

    public function dateFormat($timestamp)
    {
        return date(DateTime::getDateFormat(),$timestamp);
    }

    public function timeFormat($timestamp)
    {
        return date(DateTime::getTimeFormat(),$timestamp);
    }
}
