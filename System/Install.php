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

use Arikaim\Core\FileSystem\File;
use Arikaim\Core\System\Config;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Utils\DateTime;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Db\Schema;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Extension\ExtensionsManager;
use Arikaim\Core\View\Template;
use Arikaim\Core\View\TemplatesManager;
use Arikaim\Core\Access\Access;
use Arikaim\Core\Module\ModulesManager;

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
        $result = Arikaim::db()->createDb(Arikaim::config('db/database'));  
        if ($result == false) {
            return false;
        }          
        Arikaim::db()->initConnection(Arikaim::config('db'));     

        // Create Arikaim DB tables
        $result = $this->createDbTables();
        if ($result == false) {
            return false;
        }

        // register core events
        $this->registerCoreEvents();

        // install core modules
        $modules_manager = new ModulesManager();       
        $modules_manager->install();

        // reload seystem options
        Arikaim::options()->loadOptions();

        // insert default values in db tables
        $result = $this->initDefaultValues();

        // create admin user if not exists 
        $this->createDefaultAdminUser();

        // add date, time, number format items
        $this->initDefaultOptions();

        //Install extensions      
        $this->installExtensions();

        // install current template 
        $this->installTemplates();

        // trigger event core.install
        Arikaim::event()->trigger('core.install',Arikaim::errors()->getErrors());
        return true;
    }   

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
    } 

    private function installTemplates()
    {
        // install default template routes      
        $templates = new TemplatesManager();
        $items = $templates->scan();
        foreach ($items as $template) {
            $details = $templates->getTemlateDetails($template);
            if ($details['current'] == true) {
                $templates->install($template);
                return true;
            }
        }
        return $templates->install("default");
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
        $user = Model::Users();
        $result = $user->hasControlPanelUser();
        if ($result == true) {
            return false;
        }
        $user_id = $user->getControlPanelUser();
        if ($user_id == false) {
            $user_id = $user->getId('admin');
            if ($user_id == null) {
                $user_id = $user->createUser("admin","admin");
            }                
            if ($user_id == false) {
                Arikaim::errors()->addError('CONTROL_PANEL_USER_ERROR',"Error create control panel user.");
            }                
        }     
        $result = Model::Permissions()->setUserPermission($user_id,Access::CONTROL_PANEL,Access::FULL);
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
        // email settings
        Arikaim::options()->set('mailer.from.email','',false);
        // logger
        Arikaim::options()->set('logger',true,true);
        Arikaim::options()->set('logger.stats',true,true);
        // session
        Arikaim::options()->set('session.recreation.interval',10,false);
    }

    private function initDefaultValues() 
    {    
        $items = Arikaim::config()->loadJsonConfigFile("language.json");
        foreach ($items as $key => $item) {    
            if (isset($item['country_code']) == "") {
                $item['country_code'] = $item['code'];
            }       
            try {                           
                Model::Language()->add($item);     
            } catch(\Exception $e) {
                return false;
            }
        }
        return true;
    }

    private function createDbTables()
    {                 
        $classes = $this->getSystemSchemaClasses();
        $errors = 0;
        foreach ($classes as $class_name) {            
            $installed = Schema::install($class_name);
            if ($installed == false) {
                $errors++;
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
            $errors += Arikaim::db()->has(Arikaim::config('db/database')) ? 0:1;
            if ($errors > 0) {
                return false;
            }
            // check db tables
            $errors += Schema::schema()->hasTable('options') ? 0:1;
            $errors += Schema::schema()->hasTable('extensions') ? 0:1;
            $errors += Schema::schema()->hasTable('permissions') ? 0:1;
            $errors += Schema::schema()->hasTable('permissions_list') ? 0:1;
            $errors += Schema::schema()->hasTable('users') ? 0:1;
            $errors += Schema::schema()->hasTable('routes') ? 0:1;
            $errors += Schema::schema()->hasTable('event_subscribers') ? 0:1;
            $errors += Schema::schema()->hasTable('events') ? 0:1;
            $errors += Schema::schema()->hasTable('language') ? 0:1;
            $errors += Schema::schema()->hasTable('logs') ? 0:1;
            $errors += Schema::schema()->hasTable('jobs_queue') ? 0:1;

            $user = Model::Users();            
            $result = $user->hasControlPanelUser();           
            if ($result == false) $errors++;
           
        } catch(\Exception $e) {
            $errors++;
        }
        if ($errors > 0) {
            return false;
        }
        return true;
    }

    public function getSystemSchemaClasses()
    {
        return ['UsersSchema',
        'UserGroupsSchema',
        'UserGroupsDetailsSchema',
        'EventsSchema',
        'EventSubscribersSchema',
        'ExtensionsSchema',
        'JobsQueueSchema',
        'LanguageSchema',
        'LogsSchema',
        'OptionsSchema',
        'PermissionsSchema',
        'PermissionsListSchema',
        'RoutesSchema'];
    }

    public function getConfigDetails()
    {
        return Arikaim::config();
    }
}
