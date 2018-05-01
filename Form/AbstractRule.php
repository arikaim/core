<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Form;

use Arikaim\Core\Arikaim;
use Arikaim\Core\Interfaces\FilterInterface;

/**
 * Base class for all form validation rules
 */
abstract class AbstractRule implements FilterInterface
{    
    const INTEGER_TYPE  = 1;
    const STRING_TYPE   = 2;
    const FLOAT_TYPE    = 3;    
    const BOOLEAN_TYPE  = 4;
    const NUMBER_TYPE   = 5;
    const ITEMS_ARRAY   = 6;

    protected $min;
    protected $max;
    protected $valid;
    protected $error;
    protected $error_params;
    protected $required;

    /**
     * Constructor
     *
     * @param int|float $min_value
     * @param int|float $max_value
     * @param string $error_code
     */
    public function __construct($min_value = null, $max_value = null, $error = "NOT_VALID_VALUE_ERROR") 
    {
        $this->min = $min_value;
        $this->max = $max_value;
        $this->valid = true;
        $this->required = true;
        $this->error_params = [];
        $this->setError($error);
    }

    /**
     * Set field as required
     *
     * @param bool $required
     * @return void
     */
    public function setRequired($required)
    {
        $this->required = $required;
    }

    /**
     * Return true if field rule is required
     *
     * @return boolean
     */
    public function isRequired()
    {
        return ($this->required === false) ? false : true;
    }

    /**
     * Validate form rule
     *
     * @param mixed $value Filed value
     * @return bool
     */
    public function validate($value)
    {
        if ((empty($value) == true) && ($this->required == false)) {
            return true;
        }

        $filter = $this->getFilter();
        $filter_options = $this->getFilterOptions();
        $result = filter_var($value,$filter,$filter_options);
        $this->setValid($result);       
        return $result;
    }

    /**
     * Validate field type
     *
     * @param mixed $value
     * @param int $type
     * @return bool
     */
    protected function validateType($value, $type)
    {
        switch ($type) {
            case Self::INTEGER_TYPE: {        
                if (is_numeric($value) == true) {
                    $value = (int)$value;                   
                    if (is_int($value) == true) {
                        return true;
                    }
                }
                break;
            }
            case Self::STRING_TYPE: {
                if (is_string($value) == true) {
                    return true;
                }
                break;
            }
            case Self::FLOAT_TYPE: {
                if (is_numeric($value) == true) {
                    $value = (float)$value;
                    if (is_float($value) == true) {
                        return true;
                    }
                }
                break;
            }
            case Self::NUMBER_TYPE: {
                if (is_numeric($value) == true) {
                    return true;
                }
                break;
            }
            case Slf::ITEMS_ARRAY: {
                if (is_array($value) == true) {
                    return true;
                }
                break;
            }
            default: {
                return true;
            }
        }       
        return false;
    }

    /**
     * Minimum field value validation
     *
     * @param int|float $value
     * @param boolean $text_field
     * @return boolean
     */
    protected function validateMinValue($value, $text_field = false)
    {
        if ($this->error != "") {
            return true;
        }
        $min = ($text_field == true) ? strlen($value) : $value;

        if ($this->min != null) {                 
            return ($min < $this->min) ? false : true; 
        }
        return true;
    }

    /**
     * Maximum field value validation
     *
     * @param mixed $value
     * @param boolean $text_field Set true if field is text field
     * @return boolean
     */
    protected function validateMaxValue($value, $text_field = false)
    {
        if ($this->error != "") {
            return true;
        }
        $max = ($text_field == true) ? strlen($value) : $value;

        if ($this->max != null) {           
            return ($max > $this->max) ? false : true;                
        }
        return true;
    }

    /**
     * Return true if rule is valid
     *
     * @return boolean
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * Set rule valid status
     *
     * @param boolean $valid
     * @return void
     */
    public function setValid($valid)
    {
        $this->valid = $valid;
    }

    /**
     * Set validation error
     *
     * @param string $error
     * @return void
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    /**
     * Return filter options
     *
     * @return array
     */
    protected function getCustomFilterOptions()
    {
        return array('options' => array($this, 'customFilter'));
    }

    /**
     * Return error message
     *
     * @param array $params Error message params
     * @return string|null
     */
    public function getErrorMessage($params = []) 
    {
        if (empty($this->error) == true) {
            return "";
        }
        $this->error_params = ['min' => $this->min,'max' => $this->max];
        $this->error_params = array_merge($this->error_params,$params);  
                    
        $error_message = Arikaim::getError($this->error,$this->error_params);
        return ($error_message === false) ? $this->error : $error_message;              
    }
    
    /**
     * Set error params
     *
     * @param array $params
     * @return void
     */
    public function setErrorParams($params = [])
    {
        $this->error_params = $params;
    }

    /**
     * Return validation error
     *
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }
}
