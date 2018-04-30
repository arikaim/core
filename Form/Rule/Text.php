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

class Text extends AbstractRule
{    
    public function __construct($min_value = null, $max_value = null, $error_code = "TEXT_NOT_VALID_ERROR") 
    {
        parent::__construct($min_value,$max_value,$error_code);
    }

    public function customFilter($value) 
    {
        $this->validateType($value,"TEXT_NOT_VALID_ERROR",AbstractRule::STRING);
        $this->validateMinValue($value,"TEXT_MIN_LENGHT_ERROR",true);
        $this->validateMaxValue($value,"TEXT_MAX_LENGHT_ERROR",true);
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
