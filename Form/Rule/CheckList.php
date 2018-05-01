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
 * Check if value is in list
 */
class CheckList extends AbstractRule
{
    private $allowed_values = null;

    /**
     * Constructor
     *
     * @param array $allowed_values
     * @param string $error
     */
    public function __construct(array $allowed_values, $error = "NOT_VALID_VALUE_ERROR") 
    {
        parent::__construct(null,null,$error);
        $this->allowed_values = $allowed_values;
    }

    /**
     * Validate value
     *
     * @param mixed $value
     * @return void
     */
    public function customFilter($value) 
    {
        if ($this->allowed_values == null) {
            return false;
        }
        if (in_array($value,$this->allowed_values,false) == false) {        
            $this->setErrorParams($this->allowed_values);  
            return false;         
        } 
        return true;
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
