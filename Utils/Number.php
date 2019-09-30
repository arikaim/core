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

use Arikaim\Core\Arikaim;

/**
 * Number helper
 */
class Number 
{   
    /**
     * Default format values
     *
     * @var array
     */
    private static $default_format = [
        'name' => 'default',
        'decimals' => 2,
        'decimals_separator' => ",",
        'thousands_separator' => " "
    ]; 

    /**
     * Format number
     *
     * @param integer|float $number
     * @param string $format_name
     * @return integer|float
     */
    public static function format($number, $format_name = null)
    {
        $format = Self::getFormat($format_name);
        return number_format($number,$format['decimals'],$format['decimals_separator'],$format['thousands_separator']);
    }

    /**
     * Get format options
     *
     * @param string $name
     * @return array
     */
    public static function getFormat($name = null)
    {
        $name = ($name == null) ? Arikaim::options()->get('number.format','default') : $name;
        $formats = Arikaim::options()->get('number.format.items',Self::$default_format);
        $key = array_search($name, array_column($formats, 'name'));

        return ($key !== false) ? $formats[$key] : Self::$default_format;          
    }

    /**
     * Return true if variable is number
     *
     * @param mixed $variable
     * @return boolean
     */
    public static function isNumber($variable)
    {
        return is_numeric($variable);
    }

    /**
     * Return true if variable is float
     *
     * @param mixed $variable
     * @return boolean
     */
    public static function isFloat($variable)
    {
        return is_float($variable);
    }

    /**
     * Return 0 if variable is not number
     *
     * @param mixed $variable
     * @return integer|float
     */
    public static function getNumericValue($variable) 
    {
        return (Self::isNumber($value) == false) ? 0 : $value;
    }

    /**
     * Get integer value
     *
     * @param mixed $value
     * @return integer
     */
    public static function getInteger($value)
    {
        return intval($value);
    }

    /**
     * Get number fraction
     *
     * @param mixed $value
     * @return float
     */
    public static function getFraction($value)
    {
        return ($value - intval($value));
    }
}
