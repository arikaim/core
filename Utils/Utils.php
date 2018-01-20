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

class Utils 
{   
    public static function arraySetValue($array, $path, $value, $separator = '/') 
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
    
    public static function arrayGetValues($array, $key_search)
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

    public static function arrayGetValue($array, $path, $separator = '/') 
    {    
        if (!$path) return null;
        $path_parts = is_array($path) ? $path : explode($separator, $path);
        $ref = &$array;
        foreach ($path_parts as $key) {           
            $ref = &$ref[$key];
        }
        return $ref;                
    }

    public static function arrayMerge($array1, $array2, $prev_key = "", $full_key = "") 
    {
        $merged = $array1;
        foreach ($array2 as $key => &$value) {
            if ($full_key != "") { $full_key .= "/"; }
            $full_key .= $key;
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {     
                $merged[$key] = Self::arrayMerge($merged[$key],$value,$key,$full_key);
            } else {
                $full_key = str_replace("0/","",$full_key);
                $merged[$key] = $value;               
                $full_key = str_replace("/$prev_key/$key","",$full_key);
            }
        }
        return $merged;
    }

    public static function getClasses($php_code) 
    {
        $classes = array();
        $tokens = token_get_all($php_code);
        $count = count($tokens);
        for ($i = 2; $i < $count; $i++) {
            if ($tokens[$i - 2][0] == T_CLASS && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING && !($tokens[$i - 3] && $i - 4 >= 0 && $tokens[$i - 4][0] == T_ABSTRACT)) {
                $class_name = $tokens[$i][1];
                $classes[] = $class_name;
            }
        }
        return $classes;
    }

    public static function arrayToPath($array) 
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

    public static function parseProperties($code_text,$vars) 
    {    
        if (is_array($vars) == false) $vars = [];
        $result = preg_replace_callback("/\{\{(.*?)\}\}/",
            function ($matches) use ($vars) {
                $variable_name = trim(strtolower($matches[1]));
                if ( array_key_exists($variable_name,$vars) == true ) {
                    return $vars[$variable_name];
                }
                return "";
            },$code_text);
        if ($result == null) {
            return $code_text;
        } 
        return $result;
    }

    public static function getRandomKey()
    {
        $key = md5(uniqid(rand(), true));
        return $key;
    }

    public static function getUUID() 
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
          mt_rand(0, 0xffff), mt_rand(0, 0xffff),
          mt_rand(0, 0xffff),
          mt_rand(0, 0x0fff) | 0x4000,
          mt_rand(0, 0x3fff) | 0x8000,
          mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    public static function isValidUUID($uuid) 
    {
        return preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $uuid) === 1;
    }

    public static function isValidIp($ip_address)
    {
        $flags = FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6;
        if (filter_var($ip_address, FILTER_VALIDATE_IP, $flags) === false) {
            return false;
        }
        return true;
    }

    public static function isImplemented($class_name,$interface_name)
    {       
        $interfaces = class_implements($class_name,true);
        if (is_array($interfaces) == false) return false;

        foreach ($interfaces as $key => $interface_name) {
            $base_name = last( explode('\\',$interface_name));
            if ( $base_name == $interface_name) return true;
        }
        return false;
    }

    public static function arrraySearch($array, $key, $value) 
    {
        foreach ($array as $item) {
            if (isset($item[$key]) && $item[$key] == $value) return true;
        }
        return false;
    }
    
    public static function convertPathToUrl($file_path) 
    {
        return str_replace('\\','/',$file_path);
    }

    public static function isJSON($text)
    {
        if (is_string($text) == true) {
            return is_array(json_decode($text, true)) ? true : false;
        }
        return false;
    }

    public static function jsonEncode($text)
    {
        return json_encode($text, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
