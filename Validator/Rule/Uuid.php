<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Validator\Rule;

use Arikaim\Core\Validator\Rule;
use Arikaim\Core\Utils\Utils;

/**
 *  Uuid validation rule.Check if value is valid uuid.
 */
class Uuid extends Rule
{
    /**
     * Constructor
     *
     */
    public function __construct() 
    {
        parent::__construct();
        $this->setError("UUID_NOT_VALID_ERROR");  
    }

    /**
     * Validate value
     *
     * @param string $value
     * @return boolean
     */
    public function validate($value) 
    {
        return (Utils::isValidUUID($value) == false) ? false : true;          
    } 

    /**
     * Return filter type
     *
     * @return int
     */
    public function getType()
    {       
        return FILTER_CALLBACK;
    }
}
