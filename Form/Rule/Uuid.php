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
use Arikaim\Core\Utils\Utils;

class Uuid extends AbstractRule
{
    
    public function customFilter($value) 
    {
        if ( Utils::isValidUUID($value) == false) {
            $this->setError("UUID_NOT_VALID_ERROR");
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
