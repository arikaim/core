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
use Arikaim\Core\Db\Condition;
use Arikaim\Core\Db\Search;

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

    public static function applyCondition($model, $condition)
    {
        if (empty($condition) == true) {
            return $model;
        } 
        if ($condition instanceof Condition) {
            $condition = $condition->toArray();
        }
        
        if (is_array($condition) == false) {
            $model = $model->whereRaw($condition);
            return $model;
        }
        
        foreach ($condition as $item) {
            $model = Condition::applyCondition($model,$item);
        }
        return $model;
    }

    public static function createCondition($field_name, $operator, $value, array $conditions = null)
    {
        $condition = new Condition($field_name, $operator, $value);
        $condition->append($conditions);
        return $condition;
    }

    public static function getSearchConditions($model, $search = null)
    {
        $obj = new Search();
        return $obj->getSearchConditions($model,$search);
    }
}
