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

/**
 * Float number validation rule
 */
class FloatNumber extends AbstractRule
{       
    /**
     * Constructor
     *
     * @param int $min_value
     * @param int $max_value
     * @param string $error
     */
    public function __construct($min_value = null, $max_value = null, $error = "FLOAT_NOT_VALID_ERROR") 
    {
        parent::__construct($min_value,$max_value,$error);
    }

    /**
     * Validate value
     *
     * @param mixed $value
     * @return void
     */
    public function customFilter($value) 
    {
        $errors = 0;
        $result = $this->validateType($value,AbstractRule::FLOAT_TYPE);
        if ($result == false) {
            $this->setError("FLOAT_NOT_VALID_ERROR");
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
