<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Validator\Rule;

use Arikaim\Core\Validator\Rule;

/**
 * Check if field value is valid file array 
 */
class File extends Rule
{    
    /**
     * Constructor
     *
     * @param string $error
     */
    public function __construct($error = "NOT_VALID_VALUE_ERROR") 
    {
        parent::__construct(null,null,$error);
    }

    /**
     * Validate file data array
     *
     * @param array $value
     * @return boolean
     */
    public function customFilter($value) 
    { 
        if (is_array($value) == false) {
            return false;
        }
        if (isset($value['data']) == false) {
            // missing data 
            return false;
        }
        if (isset($value['name']) == false) {
            // missing name 
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
