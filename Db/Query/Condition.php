<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Db\Query;

use Arikaim\Core\Db\Query\BaseCondition;
use Arikaim\Core\Interfaces\QueryBuilderInterface;

/**
 * Database condition
*/
class Condition extends BaseCondition implements QueryBuilderInterface
{   
    const AND_OPERATOR = 'and';
    const OR_OPERATOR = 'or';
  
    public function __construct($field = null, $operator = null, $value = null, $statement_operator = Self::AND_OPERATOR) 
    {
        parent::__construct();
        if ($field != null) {
            $this->andCondition($field, $operator, $value);
        }
    }

    private function create($field, $operator, $value, $statement_operator = Self::AND_OPERATOR)
    {
        $condition['field'] = $field;
        $condition['operator'] = $operator;
        $condition['value'] = $value;
        $condition['statement_operator'] = $statement_operator;
        return $this->addCondition($condition);
    }

    public function andCondition($field, $operator, $value)
    {
        return $this->create($field,$operator,$value,"and");
    }

    public function orCondition($field, $operator, $value)
    {
        return $this->create($field,$operator,$value,"or");
    }

    public function apply($model, $condition)
    {
        $condition = $this->normalizeCondition($condition);
        if ($condition == false) {
            return $model;
        }
        switch($condition['statement_operator']) {
            case Self::AND_OPERATOR: {
                $model = $model->where($condition['field'],$condition['operator'],$condition['value']);
                break;
            }
            case Self::OR_OPERATOR: {
                $model = $model->orWhere($condition['field'],$condition['operator'],$condition['value']);
                break;
            }
        }
        return $model;
    }
}
