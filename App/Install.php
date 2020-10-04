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

use Arikaim\Core\System\Error\Traits\TaskErrors;
use Exception;

/**
 * Arikaim install
 */
class Install 
{
    use TaskErrors;

    const INSTALL_PAGE_URL_PATH = 'admin/install';
 
    /**
     * Get install page url
     *
     * @return string
     */
    public static function getInstallPageUrl() : string
    {
        return DOMAIN . BASE_PATH . '/' . Self::INSTALL_PAGE_URL_PATH;
    }

    /**
     * Check for install page url
     *
     * @return boolean
     */
    public static function isInstallPage()
    {
        $uri = (isset($_SERVER['REQUEST_URI']) == true) ? $_SERVER['REQUEST_URI'] : '';
       
        return (\substr($uri,-13) == Self::INSTALL_PAGE_URL_PATH);
    }

    /**
     * Return true if request is for installation 
     *
     * @return boolean
     */
    public static function isApiInstallRequest()
    {
        $uri = (isset($_SERVER['REQUEST_URI']) == true) ? $_SERVER['REQUEST_URI'] : '';
       
        return (\substr($uri,-17) == 'core/api/install/');
    }

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
     * Install Arikaim
     *
     * @return boolean;
     */
    public function install() 
    {    
        System::setTimeLimit(0);

        // clear errors before start
        Arikaim::errors()->clear();
        $this->clearErrors();

        // create database if not exists  
        $databaseName = Arikaim::config()->getByPath('db/database');
      
        if (Arikaim::db()->has($databaseName) == false) {
            $charset = Arikaim::config()->getByPath('db/charset'); 
            $collation = Arikaim::config()->getByPath('db/collation');
            $result = Arikaim::db()->createDb($databaseName,$charset,$collation); 
            if ($result == false) {
                $error = Arikaim::errors()->getError('DB_DATABASE_ERROR');
                $this->addError($error);
            }            
        }          

        Arikaim::db()->initConnection(Arikaim::config()->get('db'));     

        // Create Arikaim DB tables
        $result = $this->createDbTables();      
    
        // add control panel permisison item 
        $result = Arikaim::access()->addPermission(Access::CONTROL_PANEL,Access::CONTROL_PANEL,'Arikaim control panel access.');
        if ($result == false) {    
            if (Model::Permissions()->has(Access::CONTROL_PANEL) == false) {
                $error = Arikaim::errors()->getError('REGISTER_PERMISSION_ERROR',['name' => 'ContorlPanel']);
                $this->addError($error);
            }           
        }   

        // register core events
        $this->registerCoreEvents();

        // reload seystem options
        Arikaim::options()->load();
      
        // create admin user if not exists 
        $this->createDefaultAdminUser();
      
        // add date, time, number format items
        $this->initDefaultOptions();

        // install drivers
        $this->installDrivers();
        
        // set storage folders
        $this->initStorage();

        

        return ($this->hasError() == false);
    } 

    /**
     * Install all modules
     *
     * @return boolean
     */
    public function installModules()
    {
        System::setTimeLimit(0);

        // clear errors before start
        Arikaim::errors()->clear();
        $this->clearErrors();

        // Install modules
        $modulesManager = Arikaim::packages()->create('module');
        $modulesManager->installAllPackages();
        
        return ($this->hasError() == false);
    }

    /**
     * Install all extensions packages
     *
     * @return boolean
     */
    public function installExtensions()
    {
        System::setTimeLimit(0);

        // clear errors before start
        Arikaim::errors()->clear();
        $this->clearErrors();
        
        // Install extensions      
        $extensionManager = Arikaim::packages()->create('extension');
        $extensionManager->installAllPackages();

        return ($this->hasError() == false);
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
        $linkPath = ROOT_PATH . BASE_PATH . '/public';
        File::delete($linkPath);
        // create symlink 
        return @symlink(Arikaim::storage()->getFullPath('public'),$linkPath);      
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
        // Jobs
        Arikaim::event()->registerEvent('core.jobs.before.execute','Before run job.');
        Arikaim::event()->registerEvent('core.jobs.after.execute','After run job.');
        Arikaim::event()->registerEvent('core.jobs.queue.run','After run jobs queue.');       
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
                Arikaim::errors()->addError('CONTROL_PANEL_USER_ERROR','Error create control panel user.');
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
        // email settings
        Arikaim::options()->createOption('mailer.from.email','',false);
        // logger
        Arikaim::options()->createOption('logger',true,true);
        Arikaim::options()->createOption('logger.stats',true,true);
        Arikaim::options()->createOption('logger.driver',null,true);
        // session
        Arikaim::options()->createOption('session.recreation.interval',0,false);
        // cachek drivers
        Arikaim::options()->createOption('cache.driver',null,true);
        // library params
        Arikaim::options()->createOption('library.params',[],true);
    }

    /**
     * Install drivers
     *
     * @return void
     */
    public function installDrivers()
    {
        // cache
        Arikaim::driver()->install('filesystem','Doctrine\\Common\\Cache\\FilesystemCache','cache','Filesystem cache','Filesystem cache driver','1.8.0',[]);
    }

    /**
     * Create core db tables
     *
     * @return bool
     */
    private function createDbTables()
    {                 
        $classes = $this->getSystemSchemaClasses();
        $errors = 0;     

        foreach ($classes as $class) {        
            $installed = Schema::install($class);
                  
            if ($installed == false) {
                $errors++;       
                $error = "Error create database table '" . Schema::getTable($class) . "'";
                $this->addError($error);
            } 
        }      

        return ($errors == 0);   
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
            'DriversSchema'
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
