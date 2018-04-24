<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Db\Condition;

use Arikaim\Core\Utils\Collection;

/**
 * Database base condition
*/
abstract class BaseCondition extends Collection
{   
    const DEFAULT_OPERATOR = '=';
    const DEFAULT_STATEMENT_OPERATOR = 'and';
    
    public function __construct() 
    {
        parent::__construct();
    }

    public abstract function apply($model,$condition);

    public function addCondition($condition)
    {
        if (is_array($condition) == true) {
            array_push($this->data,$condition);
            return true;      
        }
        return false;
    }

    public function append($condition)
    {
        if ($condition instanceof BaseCondition) {
            $condition = $condition->toArray();
        }
       
        if (is_array($condition) == false) {
            return false;
        }
        $this->data = array_merge($this->data,$condition);
        return true;
    }

    public function applyConditions($model)
    {       
        foreach ($this->data as $condition) {            
            $model = $this->apply($model,$condition);
        }
        return $model;
    }

    protected function normalizeCondition($condition)
    {
        if (isset($condition['field']) == false) {
            return false;
        }
        if (isset($condition['statement_operator']) == false) {
            $condition['statement_operator'] = Self::DEFAULT_STATEMENT_OPERATOR;
        }
        if (isset($condition['operator']) == false) {
            $condition['operator'] = Self::DEFAULT_OPERATOR;
        }
        if (isset($condition['value']) == false) {
            $condition['value'] = "";
        }
        if (strtolower($condition['operator']) == 'like') {
            $condition['value'] = "%" . $condition['value'] . "%";
        }
        return $condition;
    }
}
