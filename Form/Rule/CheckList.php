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

class CheckList extends AbstractRule
{
    private $allowed_values = null;

    public function __construct($allowed_values, $error_code = "NOT_VALID_VALUE_ERROR") 
    {
        parent::__construct(null,null,$error_code);
        if (is_array($allowed_values) == true) {
            $this->allowed_values = $allowed_values;
        }
    }

    public function customFilter($value) 
    {
        if ($this->allowed_values == null) {
            $this->setError();  
        }
        if (in_array($value,$this->allowed_values,false) == false) {        
            $this->setError(null,$this->allowed_values);           
        } 
        return $this->isValid();
    } 

    public function getFilter()
    {       
        return FILTER_CALLBACK;
    }

    public function getFilterOptions()
    {
        return $this->getCustomFilterOptions();
    }
}
