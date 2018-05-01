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
 * Text field rule 
 */
class Text extends AbstractRule
{    
    /**
     * Constructor
     *
     * @param int $min_lenght
     * @param int $max_lenght
     * @param string $error Error text or error code
     */
    public function __construct($min_lenght = null, $max_lenght = null, $error = "TEXT_NOT_VALID_ERROR") 
    {
        parent::__construct($min_lenght,$max_lenght,$error);
    }

    /**
     * Verify if value is valid
     *
     * @param string $value
     * @return boolean
     */
    public function customFilter($value) 
    {
        $errors = 0;
        $result = $this->validateType($value,AbstractRule::STRING_TYPE);
        if ($result == false) {
            $this->setError("TEXT_NOT_VALID_ERROR");
            $errors++;
        } 
        $result = $this->validateMinValue($value,true);
        if ($result == false) {
            $this->setError("TEXT_MIN_LENGHT_ERROR");
            $errors++;
        }   
        $result = $this->validateMaxValue($value,true);
        if ($result == false) {
            $this->setError("TEXT_MAX_LENGHT_ERROR");
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
