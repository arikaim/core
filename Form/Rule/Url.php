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
 * Url validation rule
 */
class Url extends AbstractRule
{       
    /**
     * Constructor
     *
     * @param string $error Error code or error message
     */
    public function __construct($error = "URL_NOT_VALID_ERROR") 
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
        return FILTER_VALIDATE_URL;
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
