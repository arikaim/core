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
 * Check if field value is valid file array 
 */
class File extends AbstractRule
{    
    public function __construct($error_code = "NOT_VALID_VALUE_ERROR") 
    {
        parent::__construct(null,null,$error_code);
    }

    public function customFilter($value) 
    { 
        if (is_array($value) == false) {
            $this->setError();
        }
        if (isset($value['data']) == false) {
            // missing data 
            $this->setError();
        }
        if (isset($value['name']) == false) {
            // missing name 
            $this->setError();
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
