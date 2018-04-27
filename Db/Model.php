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

use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Utils\FunctionArguments;
use Arikaim\Core\Db\Schema;
use Arikaim\Core\Interfaces\QueryBuilderInterface;
use Arikaim\Core\Db\Query\Condition;
use Arikaim\Core\Db\Query\SearchCondition;
use Arikaim\Core\Db\Query\JoinCondition;
use Arikaim\Core\Db\Query\OrderBy;
use Arikaim\Core\Db\Query\Select;

/**
 * Database Model Factory 
*/
class Model 
{    
    public static function create($class_name, $extension_name = null) 
    {  
        $full_class_name = Self::getFullClassName($class_name,$extension_name); 
        $instance = Factory::createInstance($full_class_name);
        if (Self::isValidModel($instance) == true) {
            return $instance;
        }
        return null;
    }

    public static function getModelPath($model, $parent_field_name = "parent_id")
    {
        array_unshift($result,$model->toArray());
        while ($model != false) {
            $parent_id = $model->{$parent_field_name};
            $model = $model->where('id','=',$parent_id)->first();
            if (is_object($model) == true) {
                array_unshift($result,$model->toArray());
            }
        }
        return $result;
    }

    public static function getTreePath($class_name, $id, $extension_name = null, $parent_field_name = "parent_id")
    {
        $model = Self::create($class_name,$extension_name);
        if ($model == null) {
            return false;
        }
        $result = [];
        $model = $model->where('id','=',$id)->first();
        if (is_object($model) == false) {
            return false;
        }
        return Self::getModelPath($model,$parent_field_name);
    }

    public static function getFullClassName($class_name, $extension_name = null)
    {
        return empty($extension_name) ? Factory::getModelClass($class_name) : Factory::getExtensionModelClass($extension_name,$class_name);
    }

    public static function getConstant($class_name,$name)
    {
        return constant(Factory::getModelClass($class_name) . "::" .$name);
    }

    public static function __callStatic($name, $args)
    {  
        $extension_name = FunctionArguments::getArgument($args,0,"string");
        $create_table = FunctionArguments::getArgument($args,0,"boolean");        
        $instance = Self::create($name,$extension_name);
        if ($instance == null) {
            return null;
        }   
        if ($create_table == true) {      
            if (Schema::hasTable($instance) == false) {
                $schema_class = Schema::getModelSchemaClass($name);
                Schema::install($schema_class,$extension_name);
            }
        }
        return $instance;     
    }
    
    public static function isValidModel($instance)
    {
        return is_subclass_of($instance,"\\Illuminate\\Database\\Eloquent\\Model");
    }

    public static function buildQuery($model, $query_builder)
    {
        if (empty($query_builder) == true) {
            return $model;
        } 
        if ($query_builder instanceof QueryBuilderInterface) {
            $model = $query_builder->build($model);
        }
        return $model;
    }

    public static function createCondition($field_name, $operator, $value, $query_builder = null)
    {
        $condition = new Condition($field_name, $operator, $value);
        $condition->append($query_builder);
        return $condition;
    }
    
    public static function createJoinCondition($type = JoinCondition::LEFT_JOIN, $table_name, $field, $operator, $join_field, $query_builder = null)
    {
        $condition = new JoinCondition($type,$table_name,$field,$operator,$join_field);
        $condition->append($query_builder);
        return $condition;
    }

    public static function createSelect($fields, $query_builder = null)
    {
        $builder = new Select($fields);
        $builder->append($query_builder);
        return $builder;
    }

    public static function createSearchCondition($model_class_name, $extension_name = null, $search = null, $query_builder = null)
    {
        $condition = new SearchCondition($model_class_name,$extension_name,$search);
        $condition->append($query_builder);
        return $condition;
    }

    public static function createOrderBy($field_name, $type = OrderBy::ASC, $query_builder = null)
    {
        $order = new OrderBy($field_name,$type);
        $order->append($query_builder);
        return $order;
    }
}
