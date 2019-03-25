<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Validator\Rule;

use Arikaim\Core\Validator\Rule;

/**
 * Number form rule validation
 */
class Number extends Rule
{
    /**
     * Constructor
     *
     * @param int|float $min_value
     * @param int|float $max_value
     * @param string $error
     */
    public function __construct($min_value = null, $max_value = null, $error = "NUMBER_NOT_VALID_ERROR") 
    {
        parent::__construct($min_value,$max_value,$error);
    }
    
    /**
     * Validate number value 
     *
     * @param mixed $value
     * @return boolean
     */
    public function customFilter($value) 
    {
        $errors = 0;
        $result = $this->validateType($value,Rule::NUMBER_TYPE);
        if ($result == false) {
            $this->setError("NUMBER_NOT_VALID_ERROR");
            $errors++;
        } 
        $result = $this->validateMinValue($value);
        if ($result == false) {
            $this->setError("NUMBER_MIN_VALUE_ERROR");
            $errors++;
        }   
        $result = $this->validateMaxValue($value);
        if ($result == false) {
            $this->setError("NUMBER_MAX_VALUE_ERROR");
            $errors++;
        }
        return ($errors > 0) ? false : true;
    } 

    /**
     * Return filter type
     *
     * @return int
     */
    public function getFilter()
    {       
        return FILTER_CALLBACK;
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
