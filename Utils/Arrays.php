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

use  Arikaim\Core\System\System;

class Arrays 
{
    public static function setValue($array, $path, $value, $separator = '/') 
    {
        if (!$path) return null;   
        $segments = is_array($path) ? $path : explode($separator,$path);
        $current = &$array;
        foreach ($segments as $segment) {
            if (!isset($current[$segment]))
                $current[$segment] = array();
            $current = &$current[$segment];
        }
        $current = $value;
        return $array;
    }
    
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

    public static function toPath($array) 
    {    
        if (is_array($array) == false) {
            return false;
        }
        $path = "";
        if (count($array) > 1) {          
            for ($i = 0; $i < count($array); $i++) { 
                $path .=  $array[$i] . DIRECTORY_SEPARATOR;
            }
            $result = rtrim($path,DIRECTORY_SEPARATOR);
        } else {
            $result = end($array);
        }
        return $result;
    }

    public static function toArray($text, $separator = null) 
    {
        if ($separator == null) {
            $separator = System::getEof();
        }
        $array = explode($separator,trim($text));
        foreach ($array as $key => $item) {
            if ($item == '') {
                unset($array[$key]);
            }
        }
        return $array;
    }

    public static function toString(array $array, $separator = null) {
        if (count($array) == 0) {
            return "";
        }
        if ($separator == null) {
            $separator = System::getEof();
        }
        $string = implode($separator, $array);
        return $string;
    }

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
}
