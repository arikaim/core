<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Form\Rule;

use Arikaim\Core\Form\AbstractRule;

class RegExp extends AbstractRule
{    
    public function customFilter($value) 
    {        
    } 

    public function getFilter()
    {       
        return FILTER_VALIDATE_REGEXP;
    }

    public function getErrorName()
    {
        return "REGEXP_NOT_VALID_ERROR";
    }

    public function getFilterOptions()
    {
        return [];
    }
}
