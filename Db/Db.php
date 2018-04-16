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
use Arikaim\Core\Arikaim;

/**
 *Manage database connections
*/
class Db  
{
    private $capsule;

    public function __construct($db_config) 
    {
        $this->init($db_config);
    }

    public function init($db_config)
    {
        try {                  
            $this->capsule = new Manager();
            $this->capsule->addConnection($db_config);
            $this->capsule->setAsGlobal();
            
            // schema db             
            $this->initSchemaConnection($db_config);
            $this->capsule->bootEloquent();

        } catch(\PDOException $e) {
            Arikaim::errors()->addError('DB_CONNECTION_ERROR');
            return false;
        }      
        return true;
    }

    public function getCapsule()
    {
        return $this->capsule;
    }

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

    public function createDb($database_name) 
    {    
        if (Self::has($database_name) == true) {
            return true;
        }

        $schema = $this->capsule->getConnection('schema');
        try {
            $result = $schema->statement("CREATE DATABASE $database_name");
        } catch(\Exception $e) {
            Arikaim::errors()->addError('DB_DATABASE_ERROR');
            return false;
        }
        return $result;
    }

    public static function checkConnection($connection)
    {
        try {
            $result = $connection->statement('SELECT 1');
        } catch(\PDOException $e) {
            return false;
        }
        return true;
    }

    public function testConnection($db_config)
    {                
        try {
            $this->initSchemaConnection($db_config);     
            $this->capsule->getConnection('schema')->reconnect();
            $result = $this->checkConnection($this->capsule->getConnection('schema'));      
        } catch(\Exception $e) {   
            Arikaim::errors()->addError('DB_CONNECTION_ERROR');
            return false;
        }      
        return $result;
    }

    public function initConnection($db_config)
    {
        $this->capsule->addConnection($db_config,'new');
        $this->capsule->getDatabaseManager()->setDefaultConnection('new');
        $this->capsule->setAsGlobal();
    
        $this->initSchemaConnection($db_config);
        $this->capsule->getConnection('schema')->reconnect();

        $this->capsule->bootEloquent();
    }

    private function initSchemaConnection($db_config)
    {
        $db_config['database'] = 'information_schema';               
        $this->capsule->addConnection($db_config,"schema");
    }
}
