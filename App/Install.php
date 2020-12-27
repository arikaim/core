<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\App;

use Arikaim\Core\Utils\DateTime;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Db\Schema;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Access\Access;
use Arikaim\Core\System\System;
use Arikaim\Core\Utils\File;
use Arikaim\Core\Utils\Path;
use Exception;
use Closure;

/**
 * Arikaim install
 */
class Install 
{
    /**
     * Set config files writable
     *
     * @return bool
     */
    public static function setConfigFilesWritable()
    {
        $result = true;
        $configFile = Arikaim::config()->getConfigFile();
        if (File::isWritable($configFile) == false) {
            $result = (File::setWritable($configFile) == false) ? false : $result;
        }
        $relationsFile = PAth::CONFIG_PATH . 'relations.php';
        if (File::isWritable($relationsFile) == false) {
            $result = (File::setWritable($relationsFile) == false) ? false : $result;
        }

        return $result;
    }

    /**
     * Call closure
     *
     * @param Closure $closure
     * @param string $message
     * @return void
     */
    protected function callback($closure, $message)
    {
        if (\is_callable($closure) == true) {
            $closure($message);
        }
    }

    /**
     * Install Arikaim
     *
     * @param Closeure|null $onProgress
     * @param Closeure|null $onProgressError
     * @param Closeure|null $onProgressCompleted
     * @return boolean
     */
    public function install($onProgress = null, $onProgressError = null) 
    {         
        System::setTimeLimit(0);

        // create database if not exists  
        $databaseName = Arikaim::config()->getByPath('db/database');
      
        if (Arikaim::db()->has($databaseName) == false) {
            $charset = Arikaim::config()->getByPath('db/charset'); 
            $collation = Arikaim::config()->getByPath('db/collation');
            $result = Arikaim::db()->createDb($databaseName,$charset,$collation); 
            if ($result == false) {
                $this->callback($onProgressError,'Error create database ' . $databaseName);              
                return false;                
            }         
            $this->callback($onProgress,'Database created.');          
        }          

        // check db
        Arikaim::db()->initConnection(Arikaim::config()->get('db'));  
        if (Arikaim::db()->has($databaseName) == false) {
            $error = Arikaim::errors()->getError('DB_DATABASE_ERROR');          
            $this->callback($onProgressError,$error); 
            return false;
        }
        $this->callback($onProgress,'Database status ok.');
       
        Arikaim::options()->setStorageAdapter(Model::Options());

        // Create Arikaim DB tables
        $result = $this->createDbTables(function($class) use ($onProgress) {
            $this->callback($onProgress,'Db table model created ' . $class);
        },function($error) use ($onProgressError) {
            $this->callback($onProgressError,$error);
        });      

        if ($result !== true) {           
            return false;
        } 
        $this->callback($onProgress,'System db tables created.'); 

        // Add control panel permisison item       
        $result = Arikaim::access()->addPermission(Access::CONTROL_PANEL,Access::CONTROL_PANEL,'Arikaim control panel access.');
        if ($result == false) {    
            if (Model::Permissions()->has(Access::CONTROL_PANEL) == false) {
                $error = Arikaim::errors()->getError('REGISTER_PERMISSION_ERROR',['name' => 'ContorlPanel']);
                $this->callback($onProgressError,$error);
                return false;
            }           
        } else {
            $this->callback($onProgress,'Control panel permission added.');
        }

        // register core events
        $this->registerCoreEvents();
        $this->callback($onProgress,'Register system events');      

        // reload seystem options
        Arikaim::options()->load();
      
        // create admin user if not exists       
       
        $result = $this->createDefaultAdminUser();
        if ($result === false) {
            $this->callback($onProgressError,'Error creating control panel user');
            return false;
        }
        $this->callback($onProgress,'Control panel user created.');      
       
        // add date, time, number format items     
        $this->initDefaultOptions();
        $this->callback($onProgress,'Default system options saved.');   

        // install drivers
        $result = $this->installDrivers();
        if ($result === false) {
            $this->callback($onProgressError,'Error register cache driver');
        }

        // set storage folders              
        $this->initStorage();
        $this->callback($onProgress,'Storage folders created.'); 

        return true;
    } 

    /**
     * Install all modules
     *     
     * @param Closure|null $onProgress
     * @param Closure|null $onProgressError
     * @return boolean
     */
    public function installModules($onProgress = null, $onProgressError = null)
    {      
        System::setTimeLimit(0);

        // Install modules
        $modulesManager = Arikaim::packages()->create('module');
        $result = $modulesManager->installAllPackages($onProgress,$onProgressError);
           
        return $result;  
    }

    /**
     * Install all extensions packages
     *   
     * @param Closure|null $onProgress
     * @param Closure|null $onProgressError
     * @return boolean
     */
    public function installExtensions($onProgress = null, $onProgressError = null)
    {      
        System::setTimeLimit(0);
 
        // Install extensions      
        $extensionManager = Arikaim::packages()->create('extension');
        $result = $extensionManager->installAllPackages($onProgress,$onProgressError);

        return $result;
    }

    /**
     * Create storage folders
     *
     * @return boolean
     */
    public function initStorage()
    {   
        if (Arikaim::storage()->has('bin') == false) {          
            Arikaim::storage()->createDir('bin');
        } 

        if (Arikaim::storage()->has('public') == false) {          
            Arikaim::storage()->createDir('public');
        } 
        // delete symlink
        $linkPath = ROOT_PATH . BASE_PATH . DIRECTORY_SEPARATOR . 'public';
        $linkTarget = Arikaim::storage()->getFullPath('public') . DIRECTORY_SEPARATOR;
      
        if (@\is_link($linkPath) == false) {
            // create symlink 
            @\symlink($linkTarget,$linkPath); 
        }
      
        return true;     
    }

    /**
     * Register code events
     *
     * @return void
     */
    private function registerCoreEvents()
    {
        Arikaim::event()->registerEvent('core.extension.update','After update extension.');
        Arikaim::event()->registerEvent('core.extension.download','After download extension.');
        // Routes
        Arikaim::event()->registerEvent('core.route.disable','After disable route.');
        // UI Library
        Arikaim::event()->registerEvent('core.library.download','After download UI Library.');
        // System       
        Arikaim::event()->registerEvent('core.update','After update.'); 
    } 

    /**
     * Create default control panel user
     *
     * @return boolean
     */
    private function createDefaultAdminUser()
    {
        $user = Model::Users()->getControlPanelUser();
        if ($user == false) {
            $user = Model::Users()->createUser('admin','admin');  
            if (empty($user->id) == true) {
                $error = Arikaim::errors()->getError('CONTROL_PANEL_USER_ERROR','Error create control panel user.');
                return false;
            }    
        }
    
        return Model::PermissionRelations()->setUserPermission(Access::CONTROL_PANEL,Access::FULL,$user->id);
    }

    /**
     * Set default options
     *
     * @return void
     */
    private function initDefaultOptions()
    {
        $formats = Arikaim::config()->loadJsonConfigFile('date-time-format.json');

        // add date formats options
        Arikaim::options()->createOption('date.format.items',$formats['date'],true);
        Arikaim::options()->createOption('date.format',$formats['date']['numeric-us'],true);
        // add time format options
        Arikaim::options()->createOption('time.format.items',$formats['time'],true);    
        Arikaim::options()->createOption('time.format',$formats['time']['24-long'],true);
        // add number format options
        $items = Arikaim::config()->loadJsonConfigFile('number-format.json');
        Arikaim::options()->createOption('number.format.items',$items,true);
        Arikaim::options()->createOption('number.format','default',true);
        // primary template
        Arikaim::options()->createOption('primary.template','blog',true);
        // set default time zone 
        Arikaim::options()->createOption('time.zone',DateTime::getTimeZoneName(),false);
        // mailer
        Arikaim::options()->createOption('mailer.use.sendmail',true,true);
        Arikaim::options()->createOption('mailer.smpt.port','25',true);
        Arikaim::options()->createOption('mailer.smpt.host','',true);
        Arikaim::options()->createOption('mailer.username','',true);
        Arikaim::options()->createOption('mailer.password','',true);
        Arikaim::options()->createOption('mailer.email.compillers',[],true);
        // email settings
        Arikaim::options()->createOption('mailer.from.email','',false);
        // logger
        Arikaim::options()->createOption('logger',true,true);     
        Arikaim::options()->createOption('logger.handler','db',true);
        // session
        Arikaim::options()->createOption('session.recreation.interval',0,false);
        // library params
        Arikaim::options()->createOption('library.params',[],true);
        // language
        Arikaim::options()->createOption('current.language','en',true);        
        Arikaim::options()->createOption('default.language','en',true); 
        // page
        Arikaim::options()->createOption('current.page','',true); 
        Arikaim::options()->createOption('current.path','',true);      
    }

    /**
     * Install drivers
     *
     * @return bool
     */
    public function installDrivers()
    {
        // cache
        return Arikaim::driver()->install(
            'filesystem',
            'Doctrine\\Common\\Cache\\FilesystemCache',
            'cache',
            'Filesystem cache',
            'Filesystem cache driver',
            '1.8.0',
            []
        );
    }

    /**
     * Create core db tables
     *
     * @return string|false
     */
    private function createDbTables($onSuccess = null, $onError = null)
    {                        
        $classes = $this->getSystemSchemaClasses();
    
        foreach ($classes as $class) {     
            $installed = Schema::install($class);                  
            if ($installed === false) {                    
                $error = "Error create database table '" . Schema::getTable($class) . "'";               
                $this->callback($onError,$error);
                return $error;           
            } else {
                $this->callback($onSuccess,$class);   
            }
        }      
      
        return true;
    }

    /**
     * Set system tables rows format to dynamic
     *
     * @return bool
     */
    public function systemTablesRowFormat()
    {
        $classes = $this->getSystemSchemaClasses();
       
        foreach ($classes as $class) { 
            $tableName = Schema::getTable($class);
            if ($tableName !== true) {
                $format = Arikaim::db()->getRowFormat($tableName);
                if (\strtolower($format) != 'dynamic') {
                    Schema::setRowFormat($tableName);
                }            
            }
        }
        
        return true;
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
            $errors += Arikaim::db()->has(Arikaim::config()->getByPath('db/database')) ? 0 : 1;
            if ($errors > 0) {
                return false;
            }
            // check db tables
            $tables = Self::getSystemDbTableNames();
            foreach ($tables as $tableName) {
                $errors += Schema::hasTable($tableName) ? 0 : 1;
            }
                    
            $result = Model::Users()->hasControlPanelUser();                          
            if ($result == false) {
                $errors++;
            }          

        } catch(Exception $e) {
            $errors++;
        }

        return ($errors == 0);   
    }

    /**
     * Verify system requirements
     *
     * @return array
     */
    public static function checkSystemRequirements()
    {
        $info['items'] = [];
        $info['errors']['messages'] = '';
        $errors = [];

        // php 5.6 or above
        $phpVersion = System::getPhpVersion();
        $item['message'] = 'PHP ' . $phpVersion;
        $item['status'] = 0; // error   
        if (version_compare($phpVersion,'7.1','>=') == true) {               
            $item['status'] = 1; // ok                    
        } else {
            \array_push($errors,Arikaim::errors()->getError('PHP_VERSION_ERROR'));
        }
        \array_push($info['items'],$item);

        // PDO extension
        $item['message'] = 'PDO php extension';     
        $item['status'] = (System::hasPhpExtension('PDO') == true) ? 1 : 0;
        \array_push($info['items'],$item);

        // PDO driver
        $pdoDriver = Arikaim::config()->getByPath('db/driver');
       
        $item['message'] = $pdoDriver . 'PDO driver';
        $item['status'] = 0; // error
        if (System::hasPdoDriver($pdoDriver) == true) {
            $item['status'] = 1; // ok
        } else {
            \array_push($errors,Arikaim::errors()->getError('PDO_ERROR'));         
        }
        \array_push($info['items'],$item);

        // curl extension
        $item['message'] = 'Curl PHP extension';
        $item['status'] = (System::hasPhpExtension('curl') == true) ? 1 : 2;
           
        \array_push($info['items'],$item);

        // zip extension
        $item['message'] = 'Zip PHP extension';    
        $item['status'] = (System::hasPhpExtension('zip') == true) ? 1 : 2;

        \array_push($info['items'],$item);
        
        // GD extension 
        $item['message'] = 'GD PHP extension';      
        $item['status'] = (System::hasPhpExtension('gd') == true) ? 1 : 2;
          
        \array_push($info['items'],$item);

        // fileinfo php extension
        $item['message'] = 'fileinfo PHP extension';      
        $item['status'] = (System::hasPhpExtension('fileinfo') == true) ? 1 : 2;
          
        \array_push($info['items'],$item);

        $info['errors'] = $errors;
        
        return $info;
    }  

    /**
     * Return core migration classes
     *
     * @return array
     */
    private function getSystemSchemaClasses()
    {
        return [
            'RoutesSchema',
            'UsersSchema',
            'PermissionsSchema',
            'PermissionRelationsSchema',
            'UserGroupsSchema',
            'UserGroupMembersSchema',
            'EventsSchema',
            'EventSubscribersSchema',
            'ExtensionsSchema',
            'ModulesSchema',
            'JobsSchema',
            'LanguageSchema',
            'OptionsSchema',
            'PermissionsSchema',
            'AccessTokensSchema',
            'DriversSchema',
            'LogsSchema'
        ];
    }

    /**
     * Get core db table names
     *
     * @return array
     */
    private static function getSystemDbTableNames()
    {
        return [
            'options',         
            'extensions',
            'modules',
            'permissions',
            'permission_relations',
            'users',
            'user_groups',
            'user_group_members',
            'routes',
            'event_subscribers',
            'events',
            'language',
            'jobs',
            'access_tokens',
            'drivers'
        ];
    } 
}
