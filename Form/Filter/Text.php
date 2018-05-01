<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Form\Filter;

use Arikaim\Core\Form\AbstractFilter;

/**
 * Text filter
 */
class Text extends AbstractFilter
{  
    /**
     * Filter value, return filtered value
     *
     * @param mixed $value
     * @return mixed
     */
    public function customFilter($value) 
    {      
        $value = trim($value);      
        $value = filter_var($value,FILTER_SANITIZE_STRING);     
        return $value;
    } 

    /**
     * Return filter type
     *
     * @return int
     */
    public function getFilter()
    {       
        return FILTER_CALLBACK ;
    }

    /**
     * Return filter options
     *
     * @return array
     */
    public function getFilterOptions()
    {
        return $this->getCustomFilterOptions();
    }
}
