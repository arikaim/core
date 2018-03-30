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

class Url extends AbstractRule
{       
    public function __construct($error_code = "URL_NOT_VALID_ERROR") 
    {
        parent::__construct(null,null,$error_code);
    }

    public function customFilter($value) 
    {       
    } 

    public function getFilter()
    {       
        return FILTER_VALIDATE_URL;
    }
    
    public function getFilterOptions()
    {
        return [];
    }
}
