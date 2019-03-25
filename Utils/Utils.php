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
    public static function getClasses($php_code) 
    {
        $classes = [];
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
        return md5(uniqid(rand(), true));
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
        return (filter_var($ip_address, FILTER_VALIDATE_IP, $flags) === false) ? false : true;
    }

    public static function isImplemented($class_name,$interface_name)
    {       
        $interfaces = class_implements($class_name,true);
        if (is_array($interfaces) == false) return false;

        foreach ($interfaces as $key => $interface_name) {
            $base_name = last( explode('\\',$interface_name));
            if ($base_name == $interface_name) return true;
        }
        return false;
    }

    public static function constant($name, $default_value = null)
    {
        return (defined($name) == true) ? constant($name) : $default_value; 
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
    
    public static function jsonEncode(array $data)
    {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public static function cleanJson($text)
    {
        for ($i = 0; $i <= 31; ++$i) {
            $text = str_replace(chr($i),"",$text);
        }
        $text = str_replace(chr(127),"",$text);
        $text = Self::removeBOM($text);
        $text = stripslashes($text);
        $text = htmlspecialchars_decode($text);
        return $text;
    }

    public static function jsonDecode($text, $clean = true, $to_array = true)
    {        
        if ($clean == true) {
            $text = Self::cleanJson($text);
        }
        return json_decode($text,$to_array);
    }

    public static function getBaseClassName($full_class_name)
    {
        return last(explode('\\',$full_class_name));
    }

    public static function callStatic($class_name, $method, $args)
    {
        $callable = [$class_name,$method];
        return (is_callable($callable) == false) ? null : forward_static_call($callable,$args);
    }

    public static function call($obj, $method, $args = null)
    {
        if (is_object($obj) == true) {
            $callable = array($obj,$method);
            $class_name = get_class($obj);
        } else {
            $callable = $method; 
            $class_name = null;
        }

        if (is_callable($callable) == false) {
            if ($class_name == null) {
                $class_name = $obj;
            }
            return Self::callStatic($class_name,$method,$args);  
        }

        return (is_array($args) == true) ? call_user_func_array($callable,$args) : call_user_func($callable,$args);
    }

    public static function isUrl($text)
    {
        return (filter_var($text, FILTER_VALIDATE_URL) == true) ? true : false; 
    }

    public static function isEmail($text)
    {
        return (filter_var($text,FILTER_VALIDATE_EMAIL) == false) ? false : true;
    }
    
    public static function hasHtml($text)
    {
        return ($text != strip_tags($text)) ? true : false;
    }

    public static function removeBOM($text)
    {        
        return (strpos(bin2hex($text), 'efbbbf') === 0) ? substr($text, 3) : $text;
    }

    public static function isEmpty($value)
    {       
        return (is_object($value) == true) ? empty((array) $value) : empty($value);
    }
}
