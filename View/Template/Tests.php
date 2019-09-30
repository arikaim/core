<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\View\Template;

use Arikaim\Core\Arikaim;

/**
 * Tmplate tests functions
 */
class Tests  
{
   /**
     * Return true if var is object
     *
     * @param mixed $var
     * @return boolean
     */
    public static function isObject($var)
    {
        return is_object($var);
    }

    /**
     * Return true if var is string
     *
     * @param mixed $var
     * @return boolean
     */
    public static function isString($var)
    {
        return is_string($var);
    }

    /**
     * Return true if current auth user have permission 
     *
     * @param string $name
     * @param null|string|array $type
     * @return boolean
     */
    public static function hasAccess($name, $type = null)
    {
        return Arikaim::access()->hasAccess($name,$type);
    }
}
