<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\System;

use Arikaim\Core\Utils\DateTime;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Db\Schema;
use Arikaim\Core\Db\Model;
use Arikaim\Core\View\Template\Template;
use Arikaim\Core\Access\Access;
use Arikaim\Core\Packages\Extension\ExtensionsManager;
use Arikaim\Core\Packages\Template\TemplatesManager;
use Arikaim\Core\Packages\Module\ModulesManager;
use Arikaim\Core\Queue\Cron;

/**
 * Arikaim install (TODO error messages)
 */
class Install 
{
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
        $result = Arikaim::db()->createDb(Arikaim::config('db/database'),Arikaim::config('db/charset'),Arikaim::config('db/collation'));  
        if ($result == false) {
            Arikaim::errors()->addError('Error create database  "' . Arikaim::config('db/database') . '"');
            return false;
        }          
        Arikaim::db()->initConnection(Arikaim::config('db'));     

        // Create Arikaim DB tables
        $result = $this->createDbTables();      
        if ($result == false) {
            return false;
        }

        // add control panel permisison item 
        Model::Permissions()->add(Access::CONTROL_PANEL,Access::CONTROL_PANEL,'Arikaim control panel access.');
      
        // register core events
        $this->registerCoreEvents();

        // install core modules
        $modulesManager = new ModulesManager();       
        $modulesManager->installAllPackages();

        // reload seystem options
        Arikaim::options()->loadOptions();

        // create admin user if not exists 
        $this->createDefaultAdminUser();

        // add date, time, number format items
        $this->initDefaultOptions();

        // install drivers
        $this->installDrivers();

        // install current template 
        $templateManager = new TemplatesManager();
        $currentTemplate = $templateManager->findPackage('current',true);
        $result = $currentTemplate->install();

        //Install extensions      
        $extension_manager = new ExtensionsManager();
        $result = $extension_manager->installAllPackages();
        if ($result == false) {           
            Arikaim::errors()->addError('EXTENSION_INSTALL_ERROR');
        }

        // install cron scheduler 
        $cron = new Cron();
        $cron->install();
        
        // trigger event core.install
        Arikaim::event()->trigger('core.install',Arikaim::errors()->getErrors());
        return true;
    } 

    /**
     * Register code events
     *
     * @return void
     */
    private function registerCoreEvents()
    {
        // Extensions
        Arikaim::event()->registerEvent('core.extension.before.install','Before install extension.');
        Arikaim::event()->registerEvent('core.extension.after.install','After install extension.');
        Arikaim::event()->registerEvent('core.extension.before.uninstall','Before uninstall extension.');
        Arikaim::event()->registerEvent('core.extension.after.uninstall','After uninstall extension.');
        Arikaim::event()->registerEvent('core.extension.update','After update extension.');
        Arikaim::event()->registerEvent('core.extension.download','After download extension.');
        // Routes
        Arikaim::event()->registerEvent('core.route.register','After register route.');
        Arikaim::event()->registerEvent('core.route.disable','After disable route.');
        // Templates
        Arikaim::event()->registerEvent('core.template.install','After install template.');
        Arikaim::event()->registerEvent('core.template.uninstall','After uninstall template.');
        Arikaim::event()->registerEvent('core.template.download','After download template.');
        // UI Library
        Arikaim::event()->registerEvent('core.library.download','After download UI Library.');
        // System
        Arikaim::event()->registerEvent('core.install','After install.');
        Arikaim::event()->registerEvent('core.update','After update.');
        // Jobs
        Arikaim::event()->registerEvent('core.jobs.before.execute','Before run job.');
        Arikaim::event()->registerEvent('core.jobs.after.execute','After run job.');
        Arikaim::event()->registerEvent('core.jobs.queue.run','After run jobs queue.');
        // Storage events
        Arikaim::event()->registerEvent('core.storage.delete.file','File is deleted in storage folder.');
        Arikaim::event()->registerEvent('core.storage.write.file','File is added to storage folder.');
        Arikaim::event()->registerEvent('core.storage.rename.file','Rename file.');
        Arikaim::event()->registerEvent('core.storage.copy.file','Copy file.');
        Arikaim::event()->registerEvent('core.storage.create.dir','Create directory');
        Arikaim::event()->registerEvent('core.storage.delete.dir','Delete directory');
    } 

    /**
     * Create default control panel user
     *
     * @return void
     */
    private function createDefaultAdminUser()
    {
        $user = Model::Users()->getControlPanelUser();
        if ($user == false) {
            $user = Model::Users()->createUser("admin","admin");  
            if (empty($user->id) == true) {
                Arikaim::errors()->addError('CONTROL_PANEL_USER_ERROR',"Error create control panel user.");
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
        // add date formats options
        $items = Arikaim::config()->loadJsonConfigFile("date-format.json");      
        Arikaim::options()->createOption('date.format.items',$items,false);
        // set default date format 
        $key = array_search(1,array_column($items,'default'));
        if ($key !== false) {
            Arikaim::options()->createOption('date.format',$items[$key]['value'],true);
        }
     
        // add time format options
        $items = Arikaim::config()->loadJsonConfigFile("time-format.json");
        Arikaim::options()->createOption('time.format.items',$items,false);
        // set default time format
        $key = array_search(1,array_column($items,'default'));
        if ($key !== false) {
            Arikaim::options()->createOption('time.format',$items[$key]['value'],true);
        }

        // add number format options
        $items = Arikaim::config()->loadJsonConfigFile("number-format.json");
        Arikaim::options()->createOption('number.format.items',$items,false);
        // set default number format
        Arikaim::options()->createOption('number.format',"default",true);
        
        // set default time zone 
        Arikaim::options()->createOption('time.zone',DateTime::getDefaultTimeZoneName(),false);
        // set default template name
        Arikaim::options()->createOption('current.template',Template::getTemplateName(),true);
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
                Arikaim::errors()->addError('Error create database table "' . Schema::getTable($class) . '"');
            }
        }

        return ($errors == 0) ? true : false;         
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
            $errors += Arikaim::db()->has(Arikaim::config('db/database')) ? 0 : 1;
            if ($errors > 0) {
                return false;
            }
            // check db tables
            $tables = Self::getSystemDbTableNames();
            foreach ($tables as $tableName) {
                $errors += Schema::hasTable($tableName) ? 0:1;
            }
           
            $result = Model::Users()->hasControlPanelUser();                          
            if ($result == false) {
                $errors++;
            }
           
        } catch(\Exception $e) {
            $errors++;
        }

        return !($errors > 0);   
    }

    /**
     * Return core migration classes
     *
     * @return array
     */
    private function getSystemSchemaClasses()
    {
        return [
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
            'RoutesSchema',
            'AccessTokensSchema',
            'ApiCredentialsSchema',
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
            'api_credentials',
            'drivers'
        ];
    }
}
