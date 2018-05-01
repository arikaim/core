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

use Arikaim\Core\Utils\Collection;
use Arikaim\Core\Form\AbstractRule;
use Arikaim\Core\Form\Rule;
use Arikaim\Core\Form\Filter;

class Form extends Collection 
{
    private $rules;   
    private $filters;
    private $errors;

    private static $self;

    public function __construct($fields = null) 
    {
        $this->setFields($fields);      
        $this->errors = [];
        $this->filters = [];
        $this->rules = [];
    }

    public function addRule($field_name,AbstractRule $rule, $required = true) 
    {             
        if ($rule instanceof AbstractRule) {       
            $rule->setRequired($required);
            if (array_key_exists($field_name,$this->rules) == false) {
                $this->rules[$field_name] = [];
            }
            array_push($this->rules[$field_name],$rule);
            return true;
        } 
        throw new \Exception("Not valid rule for field: $field_name");
        return false;       
    }

    public function addFilter($field_name, AbstractFilter $filter) 
    {             
        try {
            if ($filter instanceof AbstractFilter) {
                if (array_key_exists($field_name,$this->filters) == false) {
                    $this->filters[$field_name] = [];
                }    
                array_push($this->filters[$field_name],$filter);               
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }     
        return false;
    }
    
    public function sanitize($fields = null)
    {
        return $this->filterValues($fields);
    }

    public function filterValues($fields = null) 
    {          
        if (is_array($fields) == true) {
            $this->setFields($fields);    
        }
        if (is_array($this->data) == false) {
            $this->data = [];
        }
        foreach ($this->data as $field_name => $value) {     
            $filters = $this->getFilters($field_name); 
            foreach ($filters as $filter) {
                if (is_object($filter) == true) {
                    $this->data[$field_name] = $filter->processFilter($this->data[$field_name]);
                }
            }                 
        }      
        return $this->toArray();
    }

    public function filterAndValidate(array $fields = null, \Closure $on_success = null, \Closure $on_error = null)
    {
        $this->sanitize($fields);
        return $this->validate($fields,$on_success,$on_error);
    }

    public function validateRules($field_name, $value)
    {
        $rules = $this->getRules($field_name);   
        foreach ($rules as $rule) {
            $valid = $rule->validate($value);
            if ($valid == false) {
                $error['field_name'] = $field_name;
                $error['message'] = $rule->getErrorMessage(['field_name' => $field_name]);
                $this->addError($error);
            }
        }
    }

    public function validate(array $fields = null, \Closure $on_success = null, \Closure $on_error = null)
    {
        $this->errors = [];
        $this->setFields($fields);
        foreach ($this->data as $field_name => $value) {          
            $this->validateRules($field_name,$value);
        }

        if ($this->isValid() == true) {
            if (is_callable($on_success) == true ) {
                $on_success();
            }
        } else {
            if (is_callable($on_error) == true ) {
                $on_error();
            }
        }
        return $this->isValid();   
    }

    public function setError($field_name, $message)
    {
        $error['field_name'] = $field_name;
        $error['message'] = $message;
        return $this->addError($error);
    }

    public static function sanitizeVariable($value, $type = FILTER_SANITIZE_STRING) 
    {
        $value = trim($value);
        $value = filter_var($value,$type);
        return $value;
    }

    public function isValid()
    {
        if ($this->getErrorsCount() > 0) {
            return false;
        }
        return true;
    }

    public function getFields() 
    {
        return $this->data;
    }

    public function setFields($fields) 
    {
        if (is_array($fields) == true)  {
            $this->data = $fields;
            return true;
        }
        return false;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getErrorsCount()
    {
        return count($this->errors);
    }

    private function addError($error) {
        array_push($this->errors,$error);
    }

    public function getRules($field_name)
    {
        if (isset($this->rules[$field_name]) == true) {
            return $this->rules[$field_name];
        }
        return [];
    }

    public function getFilters($field_name)
    {   
        $all = [];
        if (isset($this->filters['*']) == true) {
            $all = $this->filters['*'];
        } 
        if (isset($this->filters[$field_name]) == true) {
            return array_merge($all,$this->filters[$field_name]);
        }
        return $all;
    }

    public static function Rule()
    {
        return new Rule();
    }  

    public static function Filter()
    {
        return new Filter();
    }    
}
