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

use Arikaim\Core\Db\Query\QueryBuilder;

/**
 * Database condition
*/
class JoinCondition extends QueryBuilder
{   
    const LEFT_JOIN = 'left';
    const CROSS_JOIN = 'cross';
    const INNER_JOIN = 'inner';

    protected $table_name;
    protected $type;
    protected $join_field;
    protected $field;
    protected $operator;
    protected $statement_operator;

    public function __construct($type = Self::INNER_JOIN, $table_name, $field, $operator, $join_field, $statement_operator = Self::DEFAULT_STATEMENT_OPERATOR) 
    {
        parent::__construct();
    
        $this->field = $field;
        $this->operator = $operator;
        $this->statement_operator = $statement_operator;
        $this->table_name = $table_name;
        $this->join_field = $join_field;      
        $this->type = $type;      
        $this->append($this);
    }

    public function apply($model)
    {
        $valid = $this->validate();
        if ($valid == false) {
            return $model;
        }

        switch($this->type) {
            case Self::LEFT_JOIN: {
                $model = $model->leftJoin($this->table_name,$this->field,$this->operator,$this->join_field);
                break;
            }
            case Self::INNER_JOIN: {
                $model = $model->join($this->table_name,$this->field,$this->operator,$this->join_field);
                break;
            }
            case Self::CROSS_JOIN: {
                $model = $model->crossJoin($this->table_name,$this->field,$this->operator,$this->join_field);
                break;
            }
        }
        return $model;
    }

    protected function validate()
    {
        if (empty($this->field) == true) {
            return false;
        }
        if (empty($this->join_field) == true) {
            return false;
        }
        if (empty($this->table_name) == true) {
            return false;
        }
        if (empty($this->statement_operator) == false) {
            $this->statement_operator = Self::DEFAULT_STATEMENT_OPERATOR;
        }
        if (empty($this->operator) == false) {
            $this->operator = Self::DEFAULT_OPERATOR;
        }
        return true;
    }
}
