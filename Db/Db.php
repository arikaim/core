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
use Illuminate\Database\Eloquent\Relations\Relation;

use Arikaim\Core\Arikaim;

/**
 * Manage database connections
*/
class Db  
{
    /**
     * Capsule manager object
     *
     * @var Illuminate\Database\Capsule\Manager
     */
    private $capsule;

    /**
     * Constructor
     *
     * @param array $config
     * @param array|null $relations
     */
    public function __construct($config, $relations = null) 
    {
        $this->init($config); 
        // init relations morph map
        if (is_array($relations) == true) {                   
            Relation::morphMap($relations);                          
        }
    }

    /**
     * Get relations morph map
     *
     * @return array
     */
    public function getRelationsMap()
    {
        return Relation::$morphMap;
    }

    /**
     * Create db connection and boot Eloquent
     *
     * @param array $config
     * @return boolean
     */
    public function init($config)
    {
        try {              
            $this->capsule = new Manager();
            $this->capsule->addConnection($config);
            $this->capsule->setEventDispatcher(new \Illuminate\Events\Dispatcher());
            $this->capsule->setAsGlobal();
            // schema db             
            $this->initSchemaConnection($config);          
            $this->capsule->bootEloquent();
        } catch(\Exception $e) {
            Arikaim::errors()->addError('DB_CONNECTION_ERROR');
            return false;
        }      
        return true;
    }

    /**
     * Return capsule object
     *
     * @return object
     */
    public function getCapsule()
    {
        return $this->capsule;
    }

    /**
     *  Check if database exist
     *
     * @param string $database_name
     * @return boolean
     */
    public function has($database_name)
    {   
        try {
            $schema = $this->capsule->getConnection('schema');
            $result = $schema->select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$database_name'");            
        } catch(\Exception $e) {
            return false;
        }
        return (isset($result[0]->SCHEMA_NAME) == true) ? true : false;           
    }

    /**
     * Return true if conneciton is valid
     *
     * @param string|null $name
     * @return boolean
     */
    public function isValidConnection($name = null)
    {
        try {
            $connection = $this->capsule->getDatabaseManager()->connection($name);
            $pdo = $connection->getPdo();
        } catch(\Exception $e) {
            return false;
        }
      
        return is_object($pdo);
    }

    /**
     * Create database
     *
     * @param string $database_name
     * @return boolean
     */
    public function createDb($database_name, $charset = null, $collation = null) 
    {    
        if (Self::has($database_name) == true) {
            return true;
        }

        $schema = $this->capsule->getConnection('schema');
        try {
            $charset = ($charset != null) ? "CHARACTER SET $charset" : "";
            $collation = ($charset != null) ? "COLLATE $collation" : "";

            $result = $schema->statement("CREATE DATABASE $database_name $charset $collation");
        } catch(\Exception $e) {
            Arikaim::errors()->addError('DB_DATABASE_ERROR');
            return false;
        }
        return $result;
    }

    /**
     * Verify db connection
     *
     * @param object $connection
     * @return boolean
     */
    public static function checkConnection($connection)
    {
        try {
            $result = $connection->statement('SELECT 1');
        } catch(\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * Test db connection
     *
     * @param array $config
     * @return bool
     */
    public function testConnection($config)
    {                
        try {
            $this->initSchemaConnection($config);     
            $this->capsule->getConnection('schema')->reconnect();
            $result = $this->checkConnection($this->capsule->getConnection('schema'));      
        } catch(\Exception $e) {   
            Arikaim::errors()->addError('DB_CONNECTION_ERROR');
            return false;
        }      
        return $result;
    }

    /**
     * Init db connection
     *
     * @param array $config
     * @return void
     */
    public function initConnection($config)
    {
        $this->capsule->addConnection($config,'new');
        $this->capsule->getDatabaseManager()->setDefaultConnection('new');
        $this->capsule->setAsGlobal();
    
        $this->initSchemaConnection($config);
        $this->capsule->getConnection('schema')->reconnect();

        $this->capsule->bootEloquent();
    }

    /**
     * Add db schema conneciton
     *
     * @param array $config
     * @return void
     */
    private function initSchemaConnection($config)
    {
        $config['database'] = 'information_schema';             
        $this->capsule->addConnection($config,"schema");
    }
}
