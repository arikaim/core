<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Db;

/**
 * Search condition
*/
class SearchCondition 
{
    const AND = 'and';
    const OR = 'or';
    const NOT = 'not';
    const IN = 'in';
    const NOT_IN = 'not in';

    /**
     * Create condition array
     *
     * @param string $field_name
     * @param mixed $value
     * @param string $operator
     * @param string $query_operator
     * @return array
     */
    public static function crate($model_field_name, $search_field_name, $operator = null, $query_operator = null)
    {
        $operator = (empty($operator) == true) ? '=' : $operator;
        $tokens = explode(':',$operator);
        if (isset($tokens[1]) == true) {
            $operator_params = $tokens[1];
            $operator = $tokens[0];
        } else {
            $operator_params = null;            
        }

        $query_operator = (empty($query_operator) == true) ? 'and' : $query_operator;

        return [
            'field'           => $model_field_name,
            'search_field'    => $search_field_name,
            'operator'        => $operator,
            'operator_params' => $operator_params,
            'query_operator'  => $query_operator
        ];
    } 

    /**
     * Parse search condition.
     *
     * @param array $condition
     * @param array $search_data
     * @return array
     */
    public static function parse($condition, $search_data)
    {
        $search_field = $condition['search_field'];
        $search_value = (isset($search_data[$search_field]) == true) ? $search_data[$search_field] : '';

        if (empty($condition['operator_params']) == false && $condition['operator'] == 'like') {
            $search_value = str_replace('{value}',$search_value,$condition['operator_params']);
        }

        $condition['search_value'] = $search_value;
        return $condition;
    }
}
