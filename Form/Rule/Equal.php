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
 * Equal validation rule. 
 */
class Equal extends AbstractRule
{    
    protected $value;

    /**
     * Constructor
     *
     * @param [type] $value
     * @param string $error
     */
    public function __construct($value, $error = "NOT_VALID_VALUE_ERROR") 
    {
        parent::__construct(null,null,$error);
        $this->value = $value;
    }

    /**
     * Validate value
     *
     * @param mixed $value
     * @return boolean
     */
    public function customFilter($value) 
    { 
        return ($value != $this->value) ? false : true;
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
