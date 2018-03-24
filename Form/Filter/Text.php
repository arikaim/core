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

class Text extends AbstractFilter
{  
    public function customFilter($value) 
    {      
        $value = trim($value);      
        $value = filter_var($value,FILTER_SANITIZE_STRING);     
        return $value;
    } 

    public function getFilter()
    {       
        return FILTER_CALLBACK ;
    }

    public function getFilterOptions()
    {
        return $this->getCustomFilterOptions();
    }
}
