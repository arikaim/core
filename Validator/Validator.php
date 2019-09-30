<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Validator;

use Arikaim\Core\Collection\Collection;
use Arikaim\Core\Validator\Rule;
use Arikaim\Core\Validator\FilterBuilder;
use Arikaim\Core\Validator\RuleBuilder;
use Arikaim\Core\Arikaim;

/**
 * Data validation
 */
class Validator extends Collection 
{
    /**
     * validation rules
     *
     * @var array
     */
    private $rules;
    
    /**
     * Filters
     *
     * @var array
     */
    private $filters;

    /**
     * Validation errors
     *
     * @var array
     */
    private $errors;

    /**
     * Callback for valid event
     *
     * @var \Closure
     */
    private $on_valid = null;

    /**
     * Callback for validation fail event
     *
     * @var \Closure
     */
    private $on_fail = null;

    /**
     * Validate callback
     *
     * @var \Closure
     */
    private $validator_callback;

    /**
     * Constructor
     * 
     * @param array $data
     */
    public function __construct($data = []) 
    {
        parent::__construct($data);
        
        $this->rules = [];
        $this->errors = [];
        $this->filters = [];
    }

    /**
     * Create instance
     *
     * @param array $data
     * @return object
     */
    public static function create($data)
    {
        $data = (is_array($data) == false) ? [$data] : $data;
        return new Self($data);
    }

    /**
     * Add validation rule
     *
     * @param string $field_name
     * @param Rule|string $rule
     * @param string|null $error
     * 
     * @return Validator
     */
    public function addRule($rule, $field_name = null, $error = null) 
    {                
        if (is_string($rule) == true) {
            $rule = $this->rule()->createRule($rule,$error);
        }
        if (is_object($rule) == true) {      
            $field_name = (empty($field_name) == true) ? $rule->getFieldName() : $field_name;
            if (array_key_exists($field_name,$this->rules) == false) {
                $this->rules[$field_name] = [];
            }
            array_push($this->rules[$field_name],$rule);  
            return $this;         
        } 
        return $this;
    }

    /**
     * Return rule builder
     *
     * @return RuleBuilder
     */
    public function rule()
    {  
        return new RuleBuilder();
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
        if (is_string($filter) == true) {
            $filter = FilterBuilder::createFilter($field_name,$filter);
        }

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
     * Validate rules
     *
     * @param string $field_name
     * @param array $rules
     * @return bool
     */
    public function validateRules($field_name, $rules)
    {
        $value = $this->get($field_name,null);
        $errors = 0;
        foreach ($rules as $rule) {    
            $valid = $this->validateRule($rule,$value);
            if ($valid == false) {
                $this->setError($field_name,$rule->getErrorMessage(['field_name' => $field_name])); 
                $errors++;              
            }
        }
        return ($errors == 0);
    }

    /**
     * Validate rule
     *
     * @param Rule $rule
     * @param mxied $value
     * @return bool
     */
    public function validateRule($rule, $value)
    {
        if (empty($value) == true && $rule->isRequired() == false) {
            return true;
        }

        $type = $rule->getType();
        $rule_options = ($type == FILTER_CALLBACK) ? ['options' => [$rule, 'validate']] : [];
          
        $result = filter_var($value,$type,$rule_options);     
        return $result;
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
        
        if (is_callable($this->validator_callback) == true) {
            $this->validator_callback($this->data);
        }
          
        foreach ($this->rules as $field_name => $rules) {  
            $this->validateRules($field_name,$rules);
        }

        $valid = $this->isValid();
        if ($valid == true) {
            // run events callback
            Arikaim::event()->trigger('validator.valid',$this->data,true);
            if (empty($this->on_valid) == false) {
                $this->on_valid->call($this,$this->data);
            }          
        } else {
            // run events callback
            Arikaim::event()->trigger('validator.error',$this->getErrors(),true);
            if (empty($this->on_fail) == false) {               
                $this->on_fail->call($this,$this->getErrors());
            }           
        }

        return $valid;   
    }

    /**
     * Set validator callback
     *
     * @param \Closure $callback
     * @return void
     */
    public function validatorCallback(\Closure $callback)
    {
        $this->validator_callback = function() use($callback) {
            $callback($this->data);
        };
    }

    /**
     * Callback for not valid data
     *
     * @param \Closure $callback
     * @return void
     */
    public function onFail(\Closure $callback)
    {
        $this->on_fail = $callback;
    }

    /**
     * Callback for valid data
     *
     * @param \Closure $callback
     * @return void
     */
    public function onValid(\Closure $callback)
    {
        $this->on_valid = $callback;
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
        $error = [
            'field_name' => $field_name,
            'message'    => $message
        ];
        array_push($this->errors,$error);
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
        $all = (isset($this->filters['*']) == true) ? $this->filters['*'] : [];
        return (isset($this->filters[$field_name]) == true) ? array_merge($all,$this->filters[$field_name]) : $all;          
    }
}
