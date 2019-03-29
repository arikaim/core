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
    /**
     * Table name
     *
     * @var string
     */
    protected $table_name;

    /**
     * Create table
     *
     * @return void
     */
    abstract public function create();

    /**
     * Udate existing table
     *
     * @return void
     */
    abstract public function update();

    /**
     * Constructor
     *
     * @param string|null $table_name
     */
    public function __construct($table_name = null) 
    {
        $this->table_name = $table_name;
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
    
    /**
     * Return model table name
     *
     * @param string $class_name Model class name
     * @return boo|string
     */
    public static function getTable($class_name)
    {
        $instance = Self::createInstance($class_name);
        return (is_object($instance) == false) ? false : $instance->getTableName();         
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
        $table_name = (is_object($model) == true) ? $model->getTable() : $model;
        
        return Manager::schema()->hasTable($table_name);      
    }

    /**
     * Update table 
     *
     * @param \Closure $callback
     * @return void
     */
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

    /**
     * Checkif table exist
     *
     * @return bool
     */
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
    
    /**
     * Return shema object
     *
     * @return object
     */
    public static function schema() 
    {
        return Manager::schema();
    }

    /**
     * Run Create and Update migration
     *
     * @param string $class_name
     * @param string $extension_name
     * @return bool
     */
    public static function install($class_name, $extension_name = null) 
    {                   
        $instance = Self::createInstance($class_name,$extension_name);
        if (is_object($instance) == true) {
            try {
                $instance->create();
                $instance->update();
                return $instance->tableExists();
            } catch(\Exception $e) {
        
            }
        }
        return false;
    }
}   
