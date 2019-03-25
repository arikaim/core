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

use Arikaim\Core\Validator\Rule;

/**
 * Reg exp validation rule
 */
class RegExp extends Rule
{   
    /**
     * Constructor
     *
     * @param string $error
     */
    public function __construct($error = "REGEXP_NOT_VALID_ERROR") 
    {
        parent::__construct(null,null,$error);
    }

    public function customFilter($value) 
    {        
    } 

    /**
     * Return filter type
     *
     * @return int
     */
    public function getFilter()
    {       
        return FILTER_VALIDATE_REGEXP;
    }

    /**
     * Return filter options
     *
     * @return array
     */
    public function getFilterOptions()
    {
        return [];
    }
}
