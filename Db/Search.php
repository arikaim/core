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

use Arikaim\Core\Arikaim;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Db\SearchCondition;
use Arikaim\Core\Db\Model;

/**
 * Database search session helper
*/
class Search 
{
    /**
     * Return search value
     *
     * @param [type] $namespace
     * @return mixed|null
     */
    public static function getSearchValue($field_name, $namespace = null)
    {
        $search = Self::getSearch($namespace);
        return (isset($search[$field_name]) == true) ? $search[$field_name] : null;      
    }

    /**
     * Return current search text
     *
     * @param string|null $namespace
     * @return array
     */
    public static function getSearch($namespace = null)
    {
        return Arikaim::session()->get(Utils::createKey('search',$namespace),[]);      
    }

    /**
     * Remove all search condirtions
     *
     * @param string|null $namespace
     * @return void
     */
    public static function clearSearch($namespace = null)
    {
        Arikaim::session()->remove(Utils::createKey('search',$namespace));
    }

    /**
     * Set search data
     *
     * @param array $search_data
     * @param string|null $namespace
     * @return void
     */
    public static function setSearch($search_data, $namespace = null)
    {
        Arikaim::session()->set(Utils::createKey('search',$namespace),$search_data);      
    }

    /**
     * Return search field
     *
     * @param string $model_field_name
     * @param string|null $namespace
     * @return array|null
     */
    public static function getSearchCondition($model_field_name, $namespace = null)
    {
        $conditions = Self::getSearchConditions($namespace);
        return (isset($conditions[$model_field_name]) == true) ? $conditions[$model_field_name] : null;
    }

    /**
     * Return search field
     *
     * @param string|null $namespace
     * @return array
     */
    public static function getSearchConditions($namespace = null)
    {
        return Arikaim::session()->get(Utils::createKey('search.conditions',$namespace),[]); 
    }

    /**
     * Delete search condition
     *
     * @param string $model_field_name
     * @param string|null $namespace
     * @return void
     */
    public static function deleteSearchCondition($model_field_name, $namespace = null)
    {
        $conditions = Self::getSearchConditions($namespace);
        unset($conditions[$model_field_name]);
        Self::setSearchConditions($conditions,$namespace);
    }

    /**
     * Set search conditions
     *
     * @param array $conditions
     * @param string|null $namespace
     * @return void
     */
    public static function setSearchConditions($conditions, $namespace = null)
    {
        Arikaim::session()->set(Utils::createKey('search.conditions',$namespace),$conditions); 
    }

    /**
     * Set search field value
     *
     * @param string $model_field_name
     * @param mixed $search_field_name
     * @param string $operator
     * @param string $query_operator
     * @param string|null $namespace
     * @return void
     */
    public static function setSearchCondition($model_field_name, $namespace = null, $operator = null, $query_operator = null, $search_field_name = 'search_text')
    {
        $condition = SearchCondition::crate($model_field_name,$search_field_name,$operator,$query_operator);
        $conditions = Self::getSearchConditions($namespace);
        $conditions[$model_field_name] = $condition;
    
        Self::setSearchConditions($conditions,$namespace);
    }

    /**
     * Apply search conditions and return model object
     *
     * @param Builder|Arikaim\Core\Db\Model $builder
     * @param string|null $namespace
     * @return Builder
     */
    public static function apply($builder, $namespace = null)
    {    
        $conditions = Self::getSearchConditions($namespace); 
        foreach ($conditions as $condition) {          
            $builder = Self::applyCondition($builder,$condition,$namespace);            
        }
        return $builder;
    }

    /**
     * Apply search condition 
     *
     * @param Builder|Model $builder
     * @param array $condition
     * @param string|null $namespace
     * @return Builder
     */
    public static function applyCondition($builder, $condition, $namespace = null)
    {
        $search = Self::getSearch($namespace);
        $condition = SearchCondition::parse($condition,$search);

        if (empty($condition['search_value']) == false) {      
            if ($condition['query_operator'] == 'or') {
                $builder = $builder->orWhere($condition['field'],$condition['operator'],$condition['search_value']);
            } else {
                $builder = $builder->where($condition['field'],$condition['operator'],$condition['search_value']);
            }           
        } 
        return $builder;
    }
}