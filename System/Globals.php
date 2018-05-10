<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
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
    if ($variable == null || empty($variable) == true) {
        return $default;
    } 
    return $variable;
}
