<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Validator\Filter;

use Arikaim\Core\Validator\Filter;

/**
 * Text filter
 */
class Text extends Filter
{  
    /**
     * Filter value, return filtered value
     *
     * @param mixed $value
     * @return mixed
     */
    public function filterValue($value) 
    {      
        $value = trim($value);      
        $result = filter_var($value,FILTER_SANITIZE_STRING);     
        return ($result == false) ? $value : $result;
    } 

    /**
     * Return filter type
     *
     * @return int
     */
    public function getType()
    {       
        return FILTER_CALLBACK;
    }
}
