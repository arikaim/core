<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Db;


class Condition
{   
    const DEFAULT_OPERATOR = '=';
    const DEFAULT_STATEMENT_OPERATOR = 'and';

    private $statements;

    public function __construct($field = null, $operator = null, $value = null) 
    {
        $this->statements = [];
        if ($field != null) {
            $this->andCondition($field, $operator, $value);
        }
    }

    public function add($field, $operator, $value, $statement_operator = "and")
    {
        $condition['field'] = $field;
        $condition['operator'] = $operator;
        $condition['value'] = $value;
        $condition['statement_operator'] = $statement_operator;
        return $this->addCondition($condition);
    }

    public function addCondition($condition)
    {
        $condition = Self::normalizeCondition($condition);
        if ($condition != false) {
            array_push($this->statements,$condition);
            return true;      
        }
        return false;
    }

    public function append($conditions)
    {
        if (is_array($conditions) == false) {
            return false;
        }
        $this->statements = array_merge($this->statements,$conditions);
        return true;
    }

    public function andCondition($field, $operator, $value)
    {
        return $this->add($field,$operator,$value,"and");
    }

    public function orCondition($field, $operator, $value)
    {
        return $this->add($field,$operator,$value,"or");
    }

    private static function normalizeCondition($condition)
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

    public static function applyCondition($model, $condition)
    {
        $condition = Self::normalizeCondition($condition);
        if ($condition == false) {
            return $model;
        }
        switch($condition['statement_operator']) {
            case "and": {
                $model = $model->where($condition['field'],$condition['operator'],$condition['value']);
                break;
            }
            case "or": {
                $model = $model->orWhere($condition['field'],$condition['operator'],$condition['value']);
                break;
            }
        }
        return $model;
    }

    public function toArray()
    {
        return $this->statements;
    }
}
