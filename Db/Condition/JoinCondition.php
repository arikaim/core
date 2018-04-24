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

use Arikaim\Core\Db\Condition\BaseCondition;

/**
 * Database condition
*/
class JoinCondition extends BaseCondition
{   
    const LEFT_JOIN = 'left';
    const CROSS_JOIN = 'cross';
    const INNER_JOIN = 'inner';

    public function __construct($table_name, $field, $join_field, $type = Self::INNER_JOIN, $operator = Self::DEFAULT_OPERATOR) 
    {
        parent::__construct();
        if ($field != null) {
            $this->addCondition($table_name,$field,$join_field,$type,$operator);
        }
    }

    private function addCondition($table_name, $field, $join_field, $type = Self::INNER_JOIN, $operator = DEFAULT_OPERATOR)
    {
        $condition['table_name'] = $table_name;
        $condition['field'] = $field;
        $condition['operator'] = $operator;
        $condition['join_field'] = $join_field;
        $condition['statement_operator'] = Self::DEFAULT_STATEMENT_OPERATOR;
        $condition['type'] = $type;
        return $this->addCondition($condition);
    }

    public function apply($model,$condition)
    {
        $condition = $this->normalizeCondition($condition);
        if ($condition == false) {
            return $model;
        }
        $field = $model.getTable() . '.' . $condition['field'];
        $join_field = $condition['table_name'] . "." . $condition['join_field'];

        switch($condition['type']) {
            case Self::LEFT_JOIN: {
                $model = $model->leftJoin($condition['table_name'],$field,$condition['operator'],$join_field);
                break;
            }
            case Self::INNER_JOIN: {
                $model = $model->Join($condition['table_name'],$field,$condition['operator'],$join_field);
                break;
            }
            case Self::CROSS_JOIN: {
                $model = $model->crossJoin($condition['table_name'],$field,$condition['operator'],$join_field);
                break;
            }
        }
        return $model;
    }
}