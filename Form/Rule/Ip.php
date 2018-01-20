<?php
/**
 *  Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */

namespace Arikaim\Core\Form\Rule;

use Arikaim\Core\Form\AbstractRule;

class Ip extends AbstractRule
{
       
    public function customFilter($value) 
    {
        
    } 

    public function getFilter()
    {       
        return FILTER_VALIDATE_IP;
    }

    public function getErrorName()
    {
        return "IP_NOT_VALID_ERROR";
    }

    public function getFilterOptions()
    {
        return [];
    }

}
