<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System;

use Illuminate\Database\Capsule\Manager;
use Arikaim\Core\Utils\File;
use Arikaim\Core\System\Config;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Utils\DateTime;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Db\Schema;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Extension\ExtensionsManager;
use Arikaim\Core\View\Html\Template;
use Arikaim\Core\Models\Permissions;

class Install 
{
    public function __construct() 
    {

    }

    /**
     * Install Arikaim
     *
     * @return boolean;
     */
    public function install() 
    {    
        // clear errors before start
        Arikaim::errors()->clear();
        
        // create database if not exists 
        $has_db = Self::hasDatabase(Arikaim::config('db/database'));
        if ($has_db == false) {
            $result = $this->createDB();            
        }

        // Create Arikaim DB tables
        $result = $this->createDbTables();
        if ($result == false) {
            return false;
        }
        // reload seystem options
        Arikaim::options()->loadOptions();

        // insert  default values in db tables
        $result = $this->initDefaultValues();
        // create admin user if not exists 
        $this->createDefaultAdminUser();
        // add date, time, number format items
        $this->initDefaultOptions();

        //Install extensions      
        $this->installExtensions();
    }

    private function installExtensions()
    {
        $extension_manager = new ExtensionsManager();
        $extensins = $extension_manager->scan();
        $errors = 0;
        foreach ($extensins as $name) {
            $result = $extension_manager->install($name);
            if ($result == false) {
                $errors++;
                Arikaim::errors()->addError('EXTENSION_INSTALL_ERROR');
            }
        }
        return $errors;
    } 

    private function createDefaultAdminUser()
    {
        $user = Model::User();
        $result = $user->hasControlPanelUser();
        if ($result == true) return false;
        $user_uuid = $user->getControlPanelUser();

        if ($user_uuid == false) {
            $user_uuid = $user->createUser("admin","admin");
            if ($user_uuid == false) {
                Arikaim::errors()->addError('CONTROL_PANEL_USER_ERROR',"Error create control panel user.");
            }                
        }     
        $result = Model::Permissions()->setUserPermission($user_uuid,Permissions::CONTROL_PANEL,Permissions::FULL);
        return $result;
    }

    private function initDefaultOptions()
    {
        // add date formats options
        $items = Arikaim::config()->loadJsonConfigFile("date_format.json");
        Arikaim::options()->set('date.format.items',$items,false);
        // set default date format 
        $key = array_search(1,array_column($items, 'default'));
        if ($key !== false) {
            Arikaim::options()->set('date.format',$items[$key]['value'],true);
        }
     
        // add time format options
        $items = Arikaim::config()->loadJsonConfigFile("time_format.json");
        Arikaim::options()->set('time.format.items',$items,false);
        // set default time format
        $key = array_search(1,array_column($items, 'default'));
        if ($key !== false) {
            Arikaim::options()->set('time.format',$items[$key]['value'],true);
        }

        // add number format options
        $items = Arikaim::config()->loadJsonConfigFile("number_format.json");
        Arikaim::options()->set('number.format.items',$items,false);
        // set default number format
        Arikaim::options()->set('number.format',"default",true);
        
        // set default time zone 
        Arikaim::options()->set('time.zone',DateTime::getDefaultTimeZoneName(),false);
        // set default template name
        Arikaim::options()->set('current.template',Template::getTemplateName(),true);

        // mailer
        Arikaim::options()->set('mailer.use.sendmail',true,true);
        Arikaim::options()->set('mailer.smpt.port','25',true);
        Arikaim::options()->set('mailer.smpt.host','',true);
        Arikaim::options()->set('mailer.username','',true);
        Arikaim::options()->set('mailer.password','',true);
    }

    private function initDefaultValues() 
    {    
        $errors = 0;
        $items = Arikaim::config()->loadJsonConfigFile("language.json");
        foreach ($items as $key => $item) {    
            if (isset($item['country_code']) == "") {
                $item['country_code'] = $item['code'];
            }       
            try {                  
                $search = Model::Language()->hasLanguage($item['code']);
                if ($search == false) {
                    $language = Model::Language()->fill($item);
                    $language->setPosition();
                    $language->save();               
                }
            } catch(\Exception $e) {
                echo $e->getMessage();
            }
        }
        if ($errors == 0) $result = true;
        return $result;
    }

    private function createDbTables()
    {          
        $schema_classes_path = Schema::getDbSchemaPath();
        $errors = 0;
        $result = false;
        foreach (new \DirectoryIterator($schema_classes_path) as $file) {
            if (($file->isDot() == true) || ($file->isDir() == true)) continue;
            if ($file->getExtension() != "php") continue;
            $file_name =  $schema_classes_path . DIRECTORY_SEPARATOR . $file->getFilename();
            $classes = File::getClassesInFile($file_name);
            foreach ( $classes as $key => $class_name ) {            
                $installed = Schema::install($class_name);
                if ( $installed == false) $errors++;
            }
        }
        if ($errors == 0) $result = true;
        return $result;
    }

    /**
     * Check if system is installed.
     *
     * @return boolean
     */
    public static function isInstalled() 
    {
        $errors = 0;      
        try {
            // check db
            $errors += Self::hasDatabase(Arikaim::config('db/database')) ? 0:1;
            // check db tables
            $errors += Schema::schema()->hasTable('options') ? 0:1;
            $errors += Schema::schema()->hasTable('extensions') ? 0:1;
            $errors += Schema::schema()->hasTable('permissions') ? 0:1;
            $errors += Schema::schema()->hasTable('users') ? 0:1;
            $errors += Schema::schema()->hasTable('routes') ? 0:1;
            $errors += Schema::schema()->hasTable('events_subscribers') ? 0:1;
            $errors += Schema::schema()->hasTable('events') ? 0:1;
            $errors += Schema::schema()->hasTable('language') ? 0:1;
            $errors += Schema::schema()->hasTable('logs') ? 0:1;

            $user = Model::User();            
            $result = $user->hasControlPanelUser();           
            if ($result == false) $errors++;
           
        } catch(\Exception $e) {
            echo "msg:" . $e->getMessage();

            $errors++;
        }
        if ($errors > 0) return false;
        return true;
    }

    public function createDB() 
    {    
        $db = Arikaim::db()->getConnection('schema');
        $database_name = Arikaim::config('db/database');
        try {
            $result = $db->statement("CREATE DATABASE $database_name"); //, array('database_name' => $database_name));
        } catch(\Exception $e) {
            echo $e->getMessage();
            Arikaim::errors()->addError('DB_DATABASE_ERROR');
            return false;
        }
        return $result;
    }

    public static function hasDatabase($database_name)
    {   
        try {
            $db = Arikaim::db()->getConnection('schema');
            $result = $db->select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$database_name'");            
        } catch(\Exception $e) {
            return false;
        }
        if (isset($result[0]->SCHEMA_NAME) == true) {
            return true;
        }
        return false;
    }

    public static function checkDbConnection($connection)
    {
        try {
            $result = $connection->statement('SELECT 1');
            return $result;
        } catch(\PDOException $e) {
            return false;
        }
        return true;
    }

    public function getConfigDetails()
    {
        $info['db'] = Arikaim::config('db');
       // $info['database_host'] = Arikaim::config('db/host');
       // $info['database_type'] = Arikaim::config('db/driver');       
        return $info;
    }

    public function testDbConnection($user_name, $password)
    {
        $db = Arikaim::db();
        $db_config = Arikaim::config('db');
        $db_config['username'] = $user_name;
        $db_config['password'] = $password;
        $db_config['database'] = "INFORMATION_SCHEMA";
        try {
            $db->addConnection($db_config,'test');
            $result = $this->checkDbConnection($db->getConnection('test'));      
        } catch(\Exception $e) {   
            return false;
        }      
        return $result;
    }
}
