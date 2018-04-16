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

use Illuminate\Database\Capsule\Manager;
use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Arikaim;
use Arikaim\Core\System\Install;

/**
 * Database schema base class
*/
abstract class Schema  
{
    protected $table_name;

    abstract public function create();
    abstract public function update();

    public function __construct($table_name = null) 
    {
        if ($table_name != null) $this->table_name = $table_name;
    }

    public function getTableName() 
    {
        return $this->table_name;
    }
    
    public function createTable(\Closure $callback) 
    {
        $has_table = $this->tableExists();
        if ($has_table == true) {
            $this->update();
        } else {
            Manager::schema()->create($this->table_name,$callback);
        }
    } 

    public static function hasTable($model)
    {      
        if (Arikaim::db()->has(Arikaim::config('db/database')) == false) {
            return false;
        }
        $table_name = $model->getTable();      
        return Manager::schema()->hasTable($table_name);      
    }

    public function updateTable(\Closure $callback) 
    {
        Manager::schema()->table($this->table_name,$callback);
    } 
  
    public function dropTable() 
    {
        Manager::schema()->dropIfExists($this->table_name);
    } 

    public function tableExists() 
    {
        return Manager::schema()->hasTable($this->table_name);
    }

    public function hasColumn($column_name) 
    {
        return Manager::schema()->hasColumn($this->table_name,$column_name); 
    }
    
    public static function schema() 
    {
        return Manager::schema();
    }

    public static function createInstance($class_name, $extension_name = null)
    {
        if ($extension_name == null) {
            $schema_class_name = Self::getSchemaClass($class_name);
        } else {
            $schema_class_name = Self::getExtensionModelSchemaClass($extension_name,$class_name);  
        }
        $instance = Factory::createInstance($schema_class_name);
        if (Self::isValidSchema($instance) == false) {
            throw new \Exception("Not valid schema class '$schema_class_name'");
            return null;           
        } 
        return $instance;
    }
    
    public static function isValidSchema($instance)
    {
        return is_subclass_of($instance,"Arikaim\\Core\\Db\\Schema");
    }

    public static function install($class_name, $extension_name = null) 
    {                   
        $instance = Self::createInstance($class_name,$extension_name);
        if (is_object($instance) == true) {
            try {
                $instance->create();
                if ($instance->tableExists() == false ) {
                    return false;
                } 
                return true;
            } catch(\Exception $e) {
                return false;
            }
        }
        return false;
    }

    public static function getModelSchemaClass($model_class_name)
    {
        return $model_class_name . "Schema";
    }

    public static function getSchemaNamespace()
    {
        return "Arikaim\\Core\\Models\\Schema\\";
    }

    public static function getSchemaClass($base_class)
    {
        return Self::getSchemaNamespace() . $base_class;
    }

    public static function createExtensionModelSchema($extension_name, $base_class_name)
    {
        $full_class_name = Schema::getExtensionModelSchemaClass($extension_name, $base_class_name);      
        return Factory::createInstance($full_class_name);
    }

    public static function getExtensionModelSchemaClass($extension_name, $base_class_name)
    {
        return Self::getExtensionModelSchemaNamespace($extension_name) . "\\" . $base_class_name;
    }

    public static function getExtensionModelSchemaNamespace($extension_name)
    {
        $extension_name = ucfirst($extension_name);
        return "Arikaim\\Extensions\\$extension_name\\Models\\Schema";
    }
}   
