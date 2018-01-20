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

use Arikaim\Core\Arikaim;

class Number 
{   
    private static $default_format = ['name' => 'default','decimals' => 2,'decimals_separator' => ",",'thousands_separator' => " "]; 

    public function __construct() 
    {
       
    }

    public static function format($number,$format_name = null)
    {
        if ($format_name == null) {
            $format_name = Arikaim::options()->get('number.format','default');
        }
        $format = Self::getFormat($format_name);
        return number_format($number,$format['decimals'],$format['decimals_separator'],$format['thousands_separator']);
    }

    public static function getFormat($name)
    {
        $formats = Arikaim::options()->get('number.format.items',Self::$default_format);
        $key = array_search($name, array_column($formats, 'name'));
        if ($key !== false) {
            return $formats[$key];
        }
        return Self::$default_format;
    }

    public static function isNumber($value)
    {
        return is_numeric($value);
    }

    public static function isFloat()
    {
        return is_float($value);
    }

    public static function getNumericValue($value) 
    {
        if (Self::isNumber($value) == false) {
            return 0;
        }
        return $value;
    }

    public static function getInteger($value)
    {
        return intval($value);
    }

    public static function getFraction($value)
    {
        return ($value - intval($value));
    }
}
