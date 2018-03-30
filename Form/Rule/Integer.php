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

class Integer extends AbstractRule
{          
    public function __construct($min_value = null, $max_value = null, $error_code = "INT_NOT_VALID_ERROR") 
    {
        parent::__construct($min_value,$max_value,$error_code);
    }

    public function customFilter($value) 
    {       
        $this->validateType($value,"INT_NOT_VALID_ERROR",AbstractRule::INT);
        $this->validateMinValue($value,"NUMBER_MIN_VALUE_ERROR");
        $this->validateMaxValue($value,"NUMBER_MAX_VALUE_ERROR");
        return $this->isValid();
    } 

    public function getFilter()
    {
        if (($this->min == null) && ($this->max == null)) {
            return FILTER_VALIDATE_INT;
        } 
        $filter = FILTER_CALLBACK; 
        return $filter;
    }
    
    public function getFilterOptions()
    {
        return $this->getCustomFilterOptions();
    }
}
