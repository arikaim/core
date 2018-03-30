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

class Equal extends AbstractRule
{    
    public function __construct($value, $error_code = "NOT_VALID_VALUE_ERROR") 
    {
        parent::__construct(null,null,$error_code);
        $this->min = $value;
    }

    public function customFilter($value) 
    { 
        if ($value != $this->min) {
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
