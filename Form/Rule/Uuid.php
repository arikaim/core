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
use Arikaim\Core\Utils\Utils;

/**
 *  Uuid validation rule.Check if value is valid uuid.
 */
class Uuid extends AbstractRule
{
    /**
     * Constructor
     *
     * @param string $error Default error code or error message.
     */
    public function __construct($error = "UUID_NOT_VALID_ERROR") 
    {
        parent::__construct(null,null,$error);
    }

    /**
     * Validate value
     *
     * @param string $value
     * @return boolean
     */
    public function customFilter($value) 
    {
        return (Utils::isValidUUID($value) == false) ? false : true;          
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
