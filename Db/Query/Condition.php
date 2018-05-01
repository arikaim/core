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
class Condition extends QueryBuilder
{   
    protected $field;
    protected $operator;
    protected $value;
    protected $statement_operator;

    public function __construct($field = null, $operator = null, $value = null, $statement_operator = Self::AND_OPERATOR) 
    {
        parent::__construct();       

        $this->field = $field;
        $this->operator = $operator;
        $this->value = $value;
        $this->statement_operator = $statement_operator;
    }

    public function apply($model)
    {
        $valid = $this->validate();
        if ($valid == false) {
            return $model;
        }
        switch($this->statement_operator) {
            case Self::AND_OPERATOR: {
                $model = $model->where($this->field,$this->operator,$this->value);
                break;
            }
            case Self::OR_OPERATOR: {
                $model = $model->orWhere($this->field,$this->operator,$this->value);
                break;
            }
        }
        return $model;
    }

    protected function validate()
    {
        if (isset($this->field) == false) {
            return false;
        }
        if (isset($this->statement_operator) == false) {
            $this->statement_operator = Self::DEFAULT_STATEMENT_OPERATOR;
        }
        if (isset($this->operator) == false) {
            $this->operator = Self::DEFAULT_OPERATOR;
        }
        if (isset($this->value) == false) {
            $this->value = "";
        }
        if (strtolower($this->operator) == 'like') {
            $this->value = "%" . $this->value . "%";
        }
        return true;
    }
}
