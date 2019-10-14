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

/**
 * Utility static functions
 */
class Utils 
{   
    /**
     * Return classes from php code
     *
     * @param string $php_code
     * @return array
     */
    public static function getClasses($php_code) 
    {
        $classes = [];
        $tokens = token_get_all($php_code);
        $count = count($tokens);
        for ($i = 2; $i < $count; $i++) {
            if ($tokens[$i - 2][0] == T_CLASS && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING && !($tokens[$i - 3] && $i - 4 >= 0 && $tokens[$i - 4][0] == T_ABSTRACT)) {
                $class_name = $tokens[$i][1];
                array_push($classes,$class_name);
            }
        }
        return $classes;
    }

    /**
     * Create random key
     *
     * @return string
     */
    public static function createRandomKey()
    {
        return md5(uniqid(rand(), true));
    }

    /**
     * Create UUID
     *
     * @return string
     */
    public static function createUUID() 
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Check uuid is valid
     *
     * @param string $uuid
     * @return boolean
     */
    public static function isValidUUID($uuid) 
    {
        return preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $uuid) === 1;
    }

    /**
     * Return true if ip is valid.
     *
     * @param string $ip_address
     * @return boolean
     */
    public static function isValidIp($ip_address)
    {      
        return (filter_var($ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6) === false) ? false : true;
    }

    /**
     * Check if class implement interface 
     *
     * @param object $obj
     * @param string $interface_name
     * @return boolean
     */
    public static function isImplemented($obj, $interface_name)
    {       
        $result = $obj instanceof $interface_name;
        if ($result == true) {
            return true;
        }
        if (is_object($obj) == false && is_string($obj) == false) {
            return false;
        }

        foreach (class_parents($obj) as $sub_class) {
            if ($result == true) {
                break;
            }
            $result = Self::isImplemented($sub_class, $interface_name);
        } 
        return $result;
    }

    /**
     * Return constant value or default if constant not defined.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function constant($name, $default = null)
    {
        return (defined($name) == true) ? constant($name) : $default; 
    }

    /**
     * Convert path to url
     *
     * @param string $file_path
     * @return void
     */
    public static function convertPathToUrl($file_path) 
    {
        return str_replace('\\','/',$file_path);
    }

    /**
     * Return true if text is valid JSON 
     *
     * @param string $text
     * @return boolean
     */
    public static function isJSON($text)
    {
        if (is_string($text) == true) {
            return is_array(json_decode($text, true)) ? true : false;
        }
        return false;
    }
    
    /**
     * Encode array to JSON 
     *
     * @param array $data
     * @return string
     */
    public static function jsonEncode(array $data)
    {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Clean JSON text
     *
     * @param string $text
     * @return string
     */
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

    /**
     * Decode JSON text
     *
     * @param string $text
     * @param boolean $clean
     * @param boolean $to_array
     * @return array
     */
    public static function jsonDecode($text, $clean = true, $to_array = true)
    {        
        $text = ($clean == true) ? Self::cleanJson($text) : $text;
        return json_decode($text,$to_array);
    }

    /**
     * Return base class name
     *
     * @param string $full_class_name
     * @return string
     */
    public static function getBaseClassName($full_class_name)
    {
        return last(explode('\\',$full_class_name));
    }

    /**
     * Call static method
     *
     * @param string $class_name
     * @param string $method
     * @param array|null $args
     * @return mixed
     */
    public static function callStatic($class_name, $method, $args = null)
    {     
        return (is_callable([$class_name,$method]) == false) ? null : forward_static_call([$class_name,$method],$args);
    }

    /**
     * Call object method
     *
     * @param object $obj
     * @param string $method
     * @param array|null $args
     * @return mixed
     */
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

    /**
     * Return true if email is valid
     *
     * @param string $email
     * @return boolean
     */
    public static function isEmail($email)
    {
        return (filter_var($email,FILTER_VALIDATE_EMAIL) == false) ? false : true;
    }
    
    /**
     * Check if text contains thml tags
     *
     * @param string $text
     * @return boolean
     */
    public static function hasHtml($text)
    {
        return ($text != strip_tags($text)) ? true : false;
    }

    /**
     * Remove BOM from text
     *
     * @param string $text
     * @return void
     */
    public static function removeBOM($text)
    {        
        return (strpos(bin2hex($text), 'efbbbf') === 0) ? substr($text, 3) : $text;
    }

    /**
     * Check if variable is empty
     *
     * @param mixed $var
     * @return boolean
     */
    public static function isEmpty($var)
    {       
        return (is_object($var) == true) ? empty((array) $var) : empty($var);
    }

    /**
     * Format version text to full version format 0.0.0
     *
     * @param string $text
     * @return string
     */
    public static function formatVersion($text)
    {
        $items = explode('.',trim($text));
        $release = (isset($items[0]) == true) ? $items[0] : $text;
        $major = (isset($items[1]) == true) ? $items[1] : "0";       
        $minor = (isset($items[2]) == true) ? $items[2] : "0";
           
        return "$release.$major.$minor";
    }

    /**
     * Create key 
     *
     * @param string $text
     * @param string $path_item
     * @param string $separator
     * @return string
     */
    public static function createKey($text, $path_item = null, $separator = ".")
    {
        return (empty($path_item) == true) ? $text : $text . $separator . $path_item;     
    }

    /**
     * Return default if variable is empty
     *
     * @param mixed $variable
     * @param mixed $default
     * @return mixed
     */
    public function getDefault($variable, $default)
    {
        return (Self::isEmpty($variable) == true) ? $default : $variable;      
    }

    /**
     * Convert value to text
     *
     * @param mixed $value
     * @return string
     */
    public static function getValueAsText($value)
    {
        if (gettype($value) == "boolean") {           
            return ($value == true) ? "true" : "false"; 
        }       
        return "\"$value\"";
    }

    /**
     * Return true if variable is Closure
     *
     * @param mixed $variable
     * @return boolean
     */
    public static function isClosure($variable) 
    {
        return (is_object($variable) && ($variable instanceof \Closure));
    }

    /**
     * Create slug
     *
     * @param string $text
     * @param string $separator
     * @return string
     */
    public static function slug($text, $separator = '-')
    {
        return strtolower(preg_replace(
			['/[^\w\s]+/', '/\s+/'],
			['', $separator],
			$text
		));
    } 

    /**
     * Get memory size text.
     *
     * @param integer $size
     * @param array $labels
     * @param boolean $as_text
     * @return string|array
     */
    public static function getMemorySizeText($size, $labels = null, $as_text = true)
    {        
        if (is_array($labels) == false) {
            $labels = ['Bytes','KB','MB','GB','TB','PB','EB','ZB','YB'];
        }
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        $result['size'] = round($size / pow(1024, $power),2);
        $result['label'] = $labels[$power];
        return ($as_text == true) ? $result['size'] . $result['label'] : $result; 
    }

    /**
     * Return base class name
     *
     * @param string $class_name
     * @return string
     */
    public static function getClassBaseName($class_name)
    {
        $class_name = is_object($class_name) ? get_class($class_name) : $class_name;
        return basename(str_replace('\\', '/', $class_name));
    }
}
