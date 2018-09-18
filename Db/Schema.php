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

    /**
     * Constructor
     *
     * @param string|null $table_name
     */
    public function __construct($table_name = null) 
    {
        if ($table_name != null) {
            $this->table_name = $table_name;
        }
    }

    /**
     * Return table name
     *
     * @return string
     */
    public function getTableName() 
    {
        return $this->table_name;
    }
    
    public static function getTable($class_name)
    {
        $instance = Self::createInstance($class_name);
        if (is_object($instance) == false) {
            return false;
        }
        return $instance->getTableName();
    }

    /**
     * Create table
     *
     * @param \Closure $callback
     * @return void
     */
    public function createTable(\Closure $callback) 
    {
        if ($this->tableExists() == true) {
            $this->update();
        } else {
            Manager::schema()->create($this->table_name,$callback);
        }
    } 

    /**
     * Check if database exist.
     *
     * @param  object|string $model Table name or db model object
     * @return boolean
     */
    public static function hasTable($model)
    {      
        if (Arikaim::db()->has(Arikaim::config('db/database')) == false) {
            return false;
        }
        if (is_object($model) == true) {
            $table_name = $model->getTable();
        } else {
            $table_name = $model;
        }     
        return Manager::schema()->hasTable($table_name);      
    }

    public function updateTable(\Closure $callback) 
    {
        Manager::schema()->table($this->table_name,$callback);
    } 
    
    /**
     * Drop table
     *
     * @return void
     */
    public function dropTable() 
    {
        Manager::schema()->dropIfExists($this->table_name);
    } 

    public function tableExists() 
    {
        return Manager::schema()->hasTable($this->table_name);
    }

    /**
     * Check if table column exists.
     *
     * @param string $column_name
     * @return boolean
     */
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
                $instance->update();
                return $instance->tableExists();
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

    public static function getSchemaNamespace($extension_name = null)
    {
        if ($extension_name != null) {
            $extension_name = ucfirst($extension_name);
            return "Arikaim\\Extensions\\$extension_name\\Models\\Schema\\";
        }
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
        return Self::getSchemaNamespace($extension_name) . $base_class_name;
    }
}   
