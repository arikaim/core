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

abstract class AbstractRule implements FilterInterface
{    
    const INT       = 1;
    const STRING    = 2;
    const FLOAT     = 3;    
    const BOOLEAN   = 4;
    const NUMBER    = 5;
    const ITEMS_ARRAY = 6;

    protected $min;
    protected $max;
    protected $error;
    protected $error_code;
    protected $required;

    public function __construct($min_value = null, $max_value = null, $error_code = "NOT_VALID_VALUE_ERROR") 
    {
        $this->min = $min_value;
        $this->max = $max_value;
        $this->error = "";
        $this->required = true;
        $this->setErrorCode($error_code);
    }

    public function setRequired($required)
    {
        $this->required = $required;
    }

    public function isRequired($required)
    {
        return ($this->required === false) ? false : true;
    }

    public function validate($value)
    {
        if ((empty($value) == true) && ($this->required == false)) {
            return true;
        }

        $filter = $this->getFilter();
        $filter_options = $this->getFilterOptions();
        $result = filter_var($value,$filter,$filter_options);
        if ($result == false) {
            if ($filter != FILTER_CALLBACK) {
                $this->setError();
            }
            return false;
        }
        return true;
    }

    protected function validateType($value, $error_code, $type)
    {
        switch ($type) {
            case AbstractRule::INT : {
                if (is_int($value) == true) return true;
                break;
            }
            case AbstractRule::STRING : {
                if (is_string($value) == true) return true;
                break;
            }
            case AbstractRule::FLOAT : {
                if (is_float($value) == true) return true;
                break;
            }
            case AbstractRule::NUMBER : {
                if (is_numeric($value) == true) return true;
                break;
            }
            case AbstractRule::ITEMS_ARRAY : {
                if (is_array($value) == true) return true;
                break;
            }
        }
        $this->setErrorCode($error_code);
        $this->setError();
        return false;
    }

    protected function validateMinValue($value, $error_code, $text_field = false)
    {
        if ($this->error != "") {
            return true;
        }
        if ($text_field == true) { 
            $min = strlen($value); 
        } else {
            $min = $value;
        }

        if (empty($this->min) == false) {                 
            if ($min < $this->min) {         
                $this->setErrorCode($error_code);
                $this->setError();
                return false;
            }
        }
        return true;
    }

    protected function validateMaxValue($value, $error_code, $text_field = false)
    {
        if ($this->error != "") {
            return true;
        }
        if ($text_field == true) { 
            $max = strlen($value); 
        } else {
            $max = $value;
        }

        if (empty($this->max) == false) {           
            if ($max > $this->max) {          
                $this->setErrorCode($error_code); 
                $this->setError();            
                return false;
            }
        }
        return true;
    }

    public function isValid()
    {
        return (empty($this->error) == true) ? true : false; 
    }

    public function setError($error = null, $params = [])
    {
        if ($error == null) {
            $error = $this->getErrorMessage($params);
        }
        $this->error = $error;
    }

    public function setErrorCode($code)
    {
        $this->error_code = $code;
    }

    protected function getCustomFilterOptions()
    {
        return array('options' => array($this, 'customFilter'));
    }

    public function getErrorMessage($params = []) 
    {
        if (empty($this->error_code) == false) {
            $vars = ['min' => $this->min,'max' => $this->max];
            $vars = array_merge($vars,$params);  
                       
            $error = Arikaim::getError($this->error_code,$vars);
            return ($error === false) ? $this->error_code : $error;              
        }
        return null;
    }
    
    public function getError()
    {
        return $this->error;
    }

    public function getErrorCode()
    {
        return $this->error_code;
    }
}
