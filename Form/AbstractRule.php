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
    protected $error_name;

    public function __construct($min_value = null,$max_value = null,$error_name = "NOT_VALID_VALUE_ERROR") 
    {
        $this->min = $min_value;
        $this->max = $max_value;
        $this->error = "";
        $this->error_name = $error_name;
    }

    public function validate($value)
    {
        $filter = $this->getFilter();
        $filter_options = $this->getFilterOptions();
        $result = filter_var($value,$filter,$filter_options);
        if ($result == false) {
            if ($filter != FILTER_CALLBACK) {
                $this->setError($this->getErrorName());
            }
            return false;
        }
        return true;
    }

    protected function validateType($value,$error_name,$type)
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
        $this->setError($error_name);
        return false;
    }

    protected function validateMinValue($value,$error_name,$text_field = false)
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
                $this->setError($error_name);
                return false;
            }
        }
        return true;
    }

    protected function validateMaxValue($value,$error_name,$text_field = false)
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
                $this->setError($error_name);              
                return false;
            }
        }
        return true;
    }

    public function isValid()
    {
        if ($this->error == "") return true;
        return false;
    }

    public function setError($error_name)
    {
        $this->error = $error_name;
    }

    protected function getCustomFilterOptions()
    {
        return array('options' => array($this, 'customFilter'));
    }

    public function getError($params = []) 
    {
        $vars = ['min' => $this->min,'max' => $this->max];
        $vars = array_merge($vars,$params);                 
        return Arikaim::getError($this->error,$vars);
    }
    
    public function getErrorName()
    {
        return "NOT_VALID_VALUE_ERROR";
    }
}
