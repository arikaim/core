<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Validator;

use Arikaim\Core\Utils\Collection;
use Arikaim\Core\Validator\Rule;
use Arikaim\Core\Validator\FilterBuilder;
use Arikaim\Core\Validator\RuleBuilder;
use Arikaim\Core\Utils\Factory;

/**
 * Data validation
 */
class Validator extends Collection 
{
    protected $builder;

    private $rules;   
    private $filters;
    private $errors;

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct($data = [], $rules = []) 
    {
        parent::__construct($data);
        $this->builder = new RuleBuilder();

        $this->errors = [];
        $this->filters = [];
        $this->rules = [];
    }

    /**
     * Create instance
     *
     * @param array $data
     * @return object
     */
    public static function create($data, $rules = [])
    {
        $data = (is_array($data) == false) ? [$data] : $data;
        return new Self($data,$rules);
    }

    /**
     * Add validation rule
     *
     * @param string $field_name
     * @param Rule|string $rule
     * @param boolean $required
     * @return Validator
     */
    public function addRule($field_name, $rule, $required = true) 
    {             
        if (is_string($rule) == true) {
            $rule = $this->builder->createRule($field_name,$rule);
        }
        
        if ($rule instanceof Rule) {       
            $rule->setRequired($required);
            if (array_key_exists($field_name,$this->rules) == false) {
                $this->rules[$field_name] = [];
            }
            array_push($this->rules[$field_name],$rule);  
            return $this;         
        } 
        return $this;
    }

    public function rule()
    {  
        return $this->builder;
    }

    /**
     * Return filter builder
     *
     * @return FilterBuilder
     */
    public function filter()
    {
        return new FilterBuilder();
    }    

    /**
     * Add filter
     *
     * @param string $field_name
     * @param Filter $filter
     * @return Validator
     */
    public function addFilter($field_name, Filter $filter) 
    {                     
        if ($filter instanceof Filter) {
            if (array_key_exists($field_name,$this->filters) == false) {
                $this->filters[$field_name] = [];
            }    
            array_push($this->filters[$field_name],$filter);               
            return true;
        }           
        return $this;
    }
    
    /**
     * Sanitize form fields values
     *
     * @param array $data
     * @return Validator
     */
    public function doFilter($data = null) 
    {          
        if ($data != null) {
            $this->data = $data;
        }
        foreach ($this->data as $field_name => $value) {     
            $filters = $this->getFilters($field_name); 
            foreach ($filters as $filter) {
                if (is_object($filter) == true) {
                    $this->data[$field_name] = $filter->processFilter($this->data[$field_name]);
                }
            }                 
        }      
        return $this;
    }

    /**
     * Sanitize and validate form
     *
     * @param array $data
     * @return void
     */
    public function filterAndValidate($data = null)
    {
        return $this->doFilter($data)->validate($data);
    }

    /**
     * Validate single value
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function validateValue($key, $value)
    {
        $rules = $this->getRules($key);   
        foreach ($rules as $rule) {
            $valid = $rule->validate($value);
            if ($valid == false) {
                $error['field_name'] = $key;
                $error['message'] = $rule->getErrorMessage(['field_name' => $key]);
                $this->addError($error);
            }
        }
    }

    /**
     * Validate 
     *
     * @param array $data
     * @param array $rules
     * @return boolean
     */
    public function validate($data = null, $rules = null)
    {
        $this->errors = [];
        if (is_array($data) == true) {
            $this->data = $data;
        }

        foreach ($this->data as $field_name => $value) {          
            $this->validateValue($field_name,$value);
        }
        return $this->isValid();   
    }

    /**
     * Set validation error
     *
     * @param string $field_name
     * @param string $message
     * @return void
     */
    public function setError($field_name, $message)
    {
        $error['field_name'] = $field_name;
        $error['message'] = $message;
        return $this->addError($error);
    }

    /**
     * Sanitize form value
     *
     * @param mixed $value
     * @param int $type
     * @return void
     */
    public static function sanitizeVariable($value, $type = FILTER_SANITIZE_STRING) 
    {
        $value = trim($value);
        $value = filter_var($value,$type);
        return $value;
    }

    /**
     * Return true if form is valid
     *
     * @return boolean
     */
    public function isValid()
    {
        return ($this->getErrorsCount() > 0) ? false : true;          
    }

    /**
     * Return validation errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Return number of errors
     *
     * @return int
     */
    public function getErrorsCount()
    {
        return count($this->errors);
    }

    private function addError($error) {
        array_push($this->errors,$error);
    }

    /**
     * Return validation rules
     *
     * @param string $field_name
     * @return void
     */
    public function getRules($field_name)
    {
        return (isset($this->rules[$field_name]) == true) ? $this->rules[$field_name] : [];          
    }

    /**
     * Return form filters
     *
     * @param string $field_name
     * @return void
     */
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
}
