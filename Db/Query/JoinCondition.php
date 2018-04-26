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
class JoinCondition extends BaseCondition implements QueryBuilderInterface
{   
    const LEFT_JOIN = 'left';
    const CROSS_JOIN = 'cross';
    const INNER_JOIN = 'inner';

    public function __construct($table_name, $field, $join_field, $type = Self::INNER_JOIN, $operator = Self::DEFAULT_OPERATOR) 
    {
        parent::__construct();
        if ($field != null) {
            $this->addItem($table_name,$field,$join_field,$type,$operator);
        }
    }

    private function addItem($table_name, $field, $join_field, $type = Self::INNER_JOIN, $operator = DEFAULT_OPERATOR)
    {
        $condition['table_name'] = $table_name;
        $condition['field'] = $field;
        $condition['operator'] = $operator;
        $condition['join_field'] = $join_field;
        $condition['statement_operator'] = Self::DEFAULT_STATEMENT_OPERATOR;
        $condition['type'] = $type;
        return $this->addCondition($condition);
    }

    public function apply($model,$data)
    {
        $data = $this->normalizeCondition($data);
        if ($data == false) {
            return $model;
        }
        $field = $model->getTable() . '.' . $data['field'];
        $join_field = $data['table_name'] . "." . $data['join_field'];

        switch($data['type']) {
            case Self::LEFT_JOIN: {
                $model = $model->leftJoin($data['table_name'],$field,$data['operator'],$join_field);
                break;
            }
            case Self::INNER_JOIN: {
                $model = $model->join($data['table_name'],$field,$data['operator'],$join_field);
                break;
            }
            case Self::CROSS_JOIN: {
                $model = $model->crossJoin($data['table_name'],$field,$data['operator'],$join_field);
                break;
            }
        }
        return $model;
    }
}
