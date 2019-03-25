<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Validator;

use Arikaim\Core\Interfaces\FilterInterface;

abstract class Filter implements FilterInterface
{    
    protected $params; 
    
    public function __construct($params = []) 
    {      
        $this->params = $params;
    }

    public function processFilter($value)
    {
        $filter = $this->getFilter();
        $filter_options = $this->getFilterOptions();      
        $result = filter_var($value,$filter,$filter_options);
        return ($result == false) ? $value : $result;
    }

    protected function getCustomFilterOptions()
    {
        return array('options' => array($this, 'customFilter'));
    }
}
