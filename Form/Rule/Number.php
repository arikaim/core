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

class Number extends AbstractRule
{
    public function customFilter($value) 
    {
        $this->validateType($value,"NUMBER_NOT_VALID_ERROR",AbstractRule::NUMBER);
        $this->validateMinValue($value,"NUMBER_MIN_VALUE_ERROR");
        $this->validateMaxValue($value,"NUMBER_MAX_VALUE_ERROR");
        return $this->isValid();
    } 

    public function getFilter()
    {       
        return FILTER_CALLBACK;
    }

    public function getFilterOptions()
    {
        return $this->getCustomFilterOptions();
    }
}
