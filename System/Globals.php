<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */

/**
 * Create event obj
 *
 * @param array $params Event params
 * @return Arikaim\Core\Events\Event 
 */
function createEvent($params = [])
{
    return new Arikaim\Core\Events\Event($params);
}

/**
 * Return default value if variable is null or empty.
 *
 * @param mixed $variable
 * @param mixed $default
 * @return mixed
 */
function defaultValue($variable, $default)
{
    return (empty($variable) == true) ? $default : $variable; 
}

/**
 * Return base class name
 *
 * @param string $class_name
 * @return string
 */
function getClassBaseName($class_name)
{
    $class_name = is_object($class_name) ? get_class($class_name) : $class_name;
    return basename(str_replace('\\', '/', $class_name));
}

/**
 * Call closure
 *
 * @param mixed $value
 * @param \Closure $closure
 * @return mixed
 */
function call($value, $closure)
{
    $closure($value);
    return $value;
}