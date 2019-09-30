<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Validator;

use Arikaim\Core\Interfaces\FilterInterface;

/**
 * Base class for all filters
 */
abstract class Filter implements FilterInterface
{    
    /**
     * Filter params
     *
     * @var array
     */
    protected $params; 
    
    /**
     * Should return filter type
     *
     * @return integer
     */
    abstract public function getType();
    
    /**
     * Constructor
     *
     * @param array $params
     */
    public function __construct($params = []) 
    {      
        $this->params = $params;
    }

    /**
     * Callback  run if filter type is FILTER_CALLBACK
     *
     * @param mixed $value
     * @return mixed
     */
    public function filterValue($value)
    {
        return $value;
    }

    /**
     * Process filter
     *
     * @param mixed $value
     * @return mixed
     */
    public function processFilter($value)
    {
        $type = $this->getType();
        $filter_options = ($type == FILTER_CALLBACK) ?  ['options' => [$this, 'filterValue']] : [];
          
        $result = filter_var($value,$type,$filter_options);
        return ($result == false) ? $value : $result;
    }
}
