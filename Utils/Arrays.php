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

use Arikaim\Core\System\System;

/**
 * Array helpers
 */
class Arrays 
{
    /**
     * Return array with unique values 
     *
     * @param array $array
     * @return array
     */
    public static function unique($array) {
        return array_keys(array_flip($array));
    } 

    /**
     * Set array value
     *
     * @param array $array
     * @param string $path
     * @param mixed $value
     * @param string $separator
     * @return array
     */
    public static function setValue($array, $path, $value, $separator = '/') 
    {
        if (!$path) {
            return null;
        }   
        $segments = is_array($path) ? $path : explode($separator,$path);
        $current = &$array;
        foreach ($segments as $segment) {
            if (!isset($current[$segment])) {
                $current[$segment] = array();
            }
            $current = &$current[$segment];
        }
        $current = $value;
        return $array;
    }

    /**
     * return true if array is associative
     *
     * @param array $array
     * @return boolean
     */
    public static function isAssociative(array $array)
    {
        if (array() === $array) return false;
        return (array_keys($array) !== range(0, count($array) - 1));
    }

    /**
     * Get default value
     *
     * @param array $array
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getDefaultValue($array, $key, $default = null)
    {
        return (isset($array[$key]) == true) ? $array[$key] : $default;
    }

    /**
     * Get array value by key path
     *
     * @param array $array
     * @param string $path
     * @param string $separator
     * @return mixed
     */
    public static function getValue($array, $path, $separator = '/') 
    {    
        if (empty($path) == true) {
            return null;
        }
        $path_parts = is_array($path) ? $path : explode($separator, $path);
        $reference = &$array;
        foreach ($path_parts as $key) {           
            $reference = &$reference[$key];
        }
        return $reference;                
    }

    /**
     * Get array value
     *
     * @param array $array
     * @param string $key_search
     * @return mixed
     */
    public static function getValues($array, $key_search)
    {
        if (is_array($array) == false) return null;
        $len = strlen($key_search);
        $result = [];
        foreach ($array as $key => $value) {
            if (substr($key,0,$len) == $key_search) {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * Merge arrays
     *
     * @param array $array1
     * @param array $array2
     * @param string $prev_key
     * @param string $full_key
     * @return array
     */
    public static function merge($array1, $array2, $prev_key = "", $full_key = "") 
    {
        $result = $array1;
        if (is_array($array2) == false) {
            return $result;
        }
        foreach ($array2 as $key => &$value) {
            if ($full_key != "") { 
                $full_key .= "/"; 
            }
            $full_key .= $key;
            if (is_array($value) && isset($result[$key]) && is_array($result[$key])) {     
                $result[$key] = Self::merge($result[$key],$value,$key,$full_key);
            } else {
                $full_key = str_replace("0/","",$full_key);
                $result[$key] = $value;               
                $full_key = str_replace("/$prev_key/$key","",$full_key);
            }
        }
        return $result;
    }

    /**
     * Convert array to path
     *
     * @param array $array
     * @return string
     */
    public static function toPath(array $array) 
    {    
        $path = "";
        if (count($array) > 1) {          
            for ($i = 0; $i < count($array); $i++) { 
                $path .= $array[$i] . DIRECTORY_SEPARATOR;
            }
            $result = rtrim($path,DIRECTORY_SEPARATOR);
        } else {
            $result = end($array);
        }
        return $result;
    }

    /**
     * Convert text to array
     *
     * @param string$text
     * @param string $separator
     * @return array
     */
    public static function toArray($text, $separator = null) 
    {
        if (is_array($text) == true) {
            return $text;
        }
        $separator = (empty($separator) == true) ? System::getEof() : $separator;   
        return explode($separator,trim($text));       
    }

    /**
     * Convert array values to string 
     *
     * @param array $array
     * @param string $separator
     * @return string
     */
    public static function toString(array $array, $separator = null) {
        if (count($array) == 0) {
            return "";
        }
        $separator = (empty($separator) == true) ? System::getEof() : $separator;          
        return implode($separator, $array);
    }

    /**
     * Convert object to array
     *
     * @param object $object
     * @return array
     */
    public static function convertToArray($object) 
    {
        $reflection = new \ReflectionClass(get_class($object));
        $result = [];
        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            $name = $property->getName();
            $result[$name] = $property->getValue($object);
            $property->setAccessible(false);
        }
        return $result;
    }

    /**
     * Return true if array have sub items
     *
     * @param array $array
     * @return bool
     */
    public static function haveSubItems($array)
    {
        if (is_array($array) == false) {
            return false;
        }
        foreach ($array as $key => $value) {        
            if (is_array($array[$key]) == true) {               
                return true;
            }
        }
        return false;
    } 

    /**
     * Set default value if key not exist in array
     *
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @return array
     */
    public static function setDefault($array, $key, $value)
    {   
        if (isset($array[$key]) == false) {          
            $array[$key] = $value;
        }
        return $array;
    }

    /**
     * Slice array by keys
     *
     * @param array $array
     * @param array|string $keys
     * @return array
     */
    public static function sliceByKeys(array $array, $keys = null) {
        $keys = (empty($keys) == true) ? array_keys($array) : $keys;
        $keys = (is_array($keys) == false) ? [$keys] : $keys;
    
        return array_intersect_key($array, array_fill_keys($keys, '1'));    
    }

    /**
     * Remove empty values from array
     *
     * @param array $array
     * @return array
     */
    public static function removeEmpty(array $array)
    {
        return array_filter($array,function($value) {
            return !empty($value) || $value === 0;
        }); 
    }

    /**
     * Filer array columns
     *
     * @param array $data
     * @param array $keys
     * @return array
     */
    public static function arrayColumns(array $data, array $keys)
    {    
        $keys = array_flip($keys);
        $filtered = array_map(function($a) use($keys) {
            return array_intersect_key($a,$keys);
        },$data);

        return $filtered;
    }
}
