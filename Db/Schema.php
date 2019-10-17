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

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Builder;

use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Db\TableBlueprint;

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
     * Db storage engine
     *
     * @var string
     */
    protected $storage_engine = 'InnoDB';

    /**
     * Create table
     * 
     * @param  Arikaim\Core\Db\TableBlueprint $table
     * @return void
     */
    abstract public function create($table);

    /**
     * Update existing table
     *
     * @param  Arikaim\Core\Db\TableBlueprint $table
     * @return void
     */
    abstract public function update($table);

    /**
     * Insert or update rows in table
     *
     * @param Builder $query
     * @return void
     */
    public function seeds($query)
    {
    }

    /**
     * Constructor
     *
     * @param string|null $table_name
     */
    public function __construct($table_name = null) 
    {      
        if (empty($table_name) == false) {
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
    
    /**
     * Return model table name
     *
     * @param string $class_name Model class name
     * @return boo|string
     */
    public static function getTable($class_name)
    {
        $instance = Factory::createSchema($class_name);
        return (is_object($instance) == false) ? false : $instance->getTableName();         
    }

    /**
     * Create table
     *    
     * @return void
     */
    public function createTable()
    {
        if ($this->tableExists() == false) {                                  
            $blueprint = new TableBlueprint($this->table_name,null);
            
            $call = function() use($blueprint) {
                $blueprint->create();

                $this->create($blueprint);            
                $blueprint->engine = $this->storage_engine;               
            };
            $call(); 
            $this->build($blueprint, Manager::schema());           
        }
    } 

    /**
     * Update table 
     *
     * @return void
     */
    public function updateTable() 
    {
        if ($this->tableExists() == true) {                           
            $blueprint = new TableBlueprint($this->table_name,null);
            
            $callback = function() use($blueprint) {
                $this->update($blueprint);                                 
            };
            $callback(); 
            $this->build($blueprint, Manager::schema());           
        }       
    } 
    
    /**
     * Execute seeds
     *
     * @return mixed|false
     */
    public function runSeeds()
    {
        if ($this->tableExists() == true) {  
            $query = Manager::table($this->table_name);          
            return $this->seeds($query);
        }

        return false;
    }

    /**
     * Return true if table is empty
     *
     * @return boolean
     */
    public function isEmpty()
    {
        $query = Manager::table($this->table_name);     
        return empty($query->count() == true);
    }

    /**
     * Get query builder for table
     *
     * @param string $table_name
     * @return QueryBuilder
     */
    public static function getQuery($table_name)
    {
        return Manager::table($table_name);  
    }

    /**
     * Execute blueprint.
     *
     * @param  \Arikaim\Core\Db\TableBlueprint  $blueprint
     * @param  \Illuminate\Database\Schema\Builder  $builder
     * @return void
     */
    public function build($blueprint, Builder $builder)
    {
        $connection = $builder->getConnection();
        $grammar = $connection->getSchemaGrammar();
        $blueprint->build($connection,$grammar);
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
     * Drop table
     * 
     * @param boolean $empty_only
     * @return boolean
     */
    public function dropTable($empty_only = true) 
    {
        if ($empty_only == true && $this->isEmpty() == true) {                  
            Manager::schema()->dropIfExists($this->table_name);
        } 
        if ($empty_only == false) {
            Manager::schema()->dropIfExists($this->table_name);           
        }
        return !$this->tableExists();
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
        $instance = Factory::createSchema($class_name,$extension_name);
        if (is_object($instance) == true) {
            try {
                $instance->createTable();
                $instance->updateTable();
                $instance->runSeeds();

                return $instance->tableExists();
            } catch(\Exception $e) {
            }
        }
        return false;
    }

    /**
     * UnInstall migration
     *
     * @param string $class_name
     * @param string $extension_name
     * @param boolean $force Set to true will drop table if have rows.
     * @return bool
     */
    public static function unInstall($class_name, $extension_name = null, $force = false) 
    {                   
        $instance = Factory::createSchema($class_name,$extension_name);
        if (is_object($instance) == true) {
            try {
                return $instance->dropTable(!$force);
            } catch(\Exception $e) {
            }
        }
        return false;
    }
}   
