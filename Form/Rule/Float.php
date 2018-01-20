<?php
/**
 *  Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */

namespace Arikaim\Core\Form\Rule;


use Arikaim\Core\Form\AbstractRule;


class Float extends AbstractRule
{
       
    public function customFilter($value) 
    {
        $this->validateType($value,"FLOAT_NOT_VALID_ERROR",AbstractRule::FLOAT);
        $this->validateMinValue($value,"NUMBER_MIN_VALUE_ERROR");
        $this->validateMaxValue($value,"NUMBER_MAX_VALUE_ERROR");
        return $this->isValid();
    } 

    public function getFilter()
    {
        if ( ($this->min == null) && ($this->max == null) ) {
            return FILTER_VALIDATE_FLOAT;
        } 
        $filter = FILTER_CALLBACK; 
        return $filter;
    }

    public function getErrorName()
    {       
        return "FLOAT_NOT_VALID_ERROR";
    }

    public function getFilterOptions()
    {
        return $this->getCustomFilterOptions();
    }

}
