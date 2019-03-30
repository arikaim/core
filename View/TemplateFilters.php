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
use Arikaim\Core\Utils\Utils;

/**
 * Tmplate filer functions
 */
class TemplateFilters  
{

    /**
     * Display html tag
     *
     * @param string $content
     * @param string $name
     * @param array $attributes
     * @param boolean $single_tag
     * @param boolean $start_tag_only
     * @return void
     */
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
    
    /**
     * Convert attributes array to string
     *
     * @param array $attributes
     * @return string
     */
    public function getAttributes($attributes)
    {        
        if (is_array($attributes) == false) {
            return "";
        }
        $result = "";   
        foreach ($attributes as $key => $value) {
            if ($key == "content" || is_array($value) == true) continue;          
            $result .= " " . $this->attr($value,$key);
        }
        return $result;   
    }
    
    /**
     * Show html start tag
     *
     * @param string $name
     * @param string $attributes
     * @return void
     */
    public function htmlStartTag($name, $attributes)
    {        
        return $this->htmlTag(null,$name,$attributes,false,true);
    }
    
    /**
     * Show html single tag
     *
     * @param string $name
     * @param string $attributes
     * @return void
     */
    public function htmlSingleTag($name, $attributes)
    {        
        return $this->htmlTag(null,$name,$attributes,true);
    }
    
    /**
     * Show html attribute
     *
     * @param string $value
     * @param string $name
     * @param string $default
     * @return void
     */
    public function attr($value, $name = null, $default = null)
    {   
        $value = (empty($value) == true) ? $default : $value;
        return (empty($value) == false) ? "$name=\"$value\"" : "";
    }

    /**
     * Display array
     *
     * @param mixed $var
     * @return string
     */
    public function showArray($var)
    {
        return (is_array($var) == true) ? print_r($var,true) : "$var";         
    }

    /**
     * Domp varibale
     *
     * @param  mixed $var
     * @return string
     */
    public function dump($var)
    {
        return var_dump($var);
    }
    
    /**
     * Check if $var1 = $var2 
     *
     * @param mixed $var1
     * @param mixed $var2
     * @param mixed $return_value
     * @return mixed|null
     */
    public function is($var1, $var2, $return_value)
    {
        if (is_array($var1) == true) {
            if (in_array($var1,$var2) == true) {
                return $return_value;
            }
        }
        if ($var1 === 'false') $var1 = false;
        if ($var1 === 'true')  $var1 = true;

        return ($var1 == $var2) ? $return_value : null;                
    }

    /**
     * Return formated date
     *
     * @param integer $timestamp
     * @param string $format
     * @return string
     */
    public function dateFormat($timestamp, $format = null)
    {
        $format = (empty($format) == true) ? DateTime::getTimeFormat() : $format;        
        return date($format,$timestamp);
    }

    /**
     * Return formated time
     *
     * @param integer $timestamp
     * @param string $format
     * @return string
     */
    public function timeFormat($timestamp, $format = null)
    {
        $format = (empty($format) == true) ? DateTime::getTimeFormat() : $format;        
        return date($format,$timestamp);
    }

    /**
     * Return default value
     *
     * @param mixed $value
     * @param mixed $default
     * @return mixed
     */
    public function getDefaultValue($value, $default)
    {
        return (Utils::isEmpty($value) == true) ? $default : $value;      
    }
}
