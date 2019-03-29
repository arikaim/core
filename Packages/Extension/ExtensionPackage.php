<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Packages\Extension;

use Arikaim\Core\Packages\Package;
use Arikaim\Core\System\Path;
use Arikaim\Core\System\Url;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Db\Model;
use Arikaim\Core\View\Template;
use Arikaim\Core\FileSystem\File;
use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Db\Schema;

/**
 * Extension Package class
*/
class ExtensionPackage extends Package
{
    const USER = 0;
    const SYSTEM = 1;
    const TYPE_NAME = ['user','system'];

    protected $porperties_list = ['path','name','title','description','version','requires','image','files','themes','params','framework'];
    
    public function __construct($properties) 
    {
        // set default
        $properties->set('type',Self::getTypeID($properties->get('type')));
        parent::__construct($properties);
    }

    public function getProperties($full = false)
    {
        $extension = Model::Extensions();

        $default_class_name = ucfirst($this->properties['name']);
        $base_class_name = $this->properties->get('class',$default_class_name);
        
        $this->properties['installed'] = $extension->isInstalled($this->properties['name']);       
        $this->properties['status'] = $extension->getStatus($this->properties['name']);

        if ($full == true) { 
            $this->properties['routes'] =  Model::Routes()->getRoutes(null,$this->properties['name']);
            $this->properties['events'] = Model::Events()->getEvents($this->properties['name']);
            $this->properties['subscribers'] = Model::EventSubscribers()->getExtensionSubscribers($this->properties['name']);
            $this->properties['database'] = $this->getModels();
            $this->properties['components'] = $this->getHtmlComponents(); 
            $this->properties['pages'] = $this->getTemplatePages(); 
            $this->properties['macros'] = $this->getTemplateMacros(); 
            $this->properties['console_commands'] = $this->getConsoleCommands();
            $this->properties['jobs'] = $this->getExtensionJobs();
        }
        return $this->properties; 
    }

    public function getExtensionJobs()
    {
        $path = Path::getExtensionJobsPath($this->getName());
        $result = [];
        foreach (new \DirectoryIterator($path) as $file) {
            if (($file->isDot() == true) || ($file->isDir() == true)) continue;
            if ($file->getExtension() != 'php') continue;

            $item['base_class'] = str_replace(".php","",$file->getFilename());
            $job = Factory::createJob($item['base_class'],$this->getName());
            if (is_object($job) == true) {
                $item['name'] = $job->getName();
                array_push($result,$item);
            }
        }
        return $result;
    }

    public function getConsoleCommands()
    {
        $extension = Model::Extensions()->where('name','=',$this->getName())->first();
        if (is_object($extension) == false) {
            return [];
        }
        $result = [];
        foreach ($extension->console_commands as $class) {
            $command = Factory::createInstance($class);
            $item['name'] = $command->getName();
            $item['title'] = $command->getDescription();      
            $item['help'] = "php cli " . $command->getName();         
            array_push($result,$item);
        }      
        return $result;      
    }

    public function getTemplateMacros()
    {
        $path = Path::getExtensionMacrosPath($this->getName());
        return Template::getMacros($path);
    }

    public function getTemplatePages()
    {
        $path = Path::getExtensionPagesPath($this->getName());
        return Template::getPages($path);
    }

    public function getModels()
    {      
        $path = Path::getExtensionModelsSchemaPath($this->getName());
        if (File::exists($path) == false) {
            return [];
        }
        $result = [];
        foreach (new \DirectoryIterator($path) as $file) {
            if (($file->isDot() == true) || ($file->isDir() == true)) continue;
            if ($file->getExtension() != 'php') continue;

            $file_name = $file->getFilename();
            $base_class = str_replace(".php","",$file_name);
            $model_obj = Factory::createSchema($base_class,$this->getName());

            if (is_subclass_of($model_obj,'Arikaim\Core\Db\Schema') == true) {               
                $item['name'] = $model_obj->getTableName();               
                array_push($result,$item);
            }
        }     
        return $result;
    }

    public function getHtmlComponents()
    {
        $path = Path::getExtensionComponentsPath($this->getName());
        return Template::getComponents($path);
    }

    public function install()
    {
        // clear extension cache
        Arikaim::cache()->deleteExtensionItems();

        $details = $this->getProperties(true);
        $extension_name = $this->getName();

        $ext_obj = Factory::createExtension($extension_name,$details->get('class'));
        if (is_object($ext_obj) == false) {
            Arikaim::errors()->addError("EXTENSION_CLASS_NOT_VALID");
            return false;
        }
       
        // trigger core.extension.before.install event
        Arikaim::event()->trigger('core.extension.before.install',$details->toArray());
        $ext_obj->onBeforeInstall();
    
        // delete extension routes
        Model::Routes()->deleteExtensionRoutes($extension_name);

        // delete jobs 
        //Arikaim::jobs()->deleteExtensionJobs($extension_name);

        // delete registered events
        Model::Events()->deleteEvents($extension_name);         
        // delete registered events subscribers
        Model::EventSubscribers()->deleteSubscribers($extension_name);

        // run install extension
        $ext_obj->install(); 
        // get console commands classes
        $details->set('console_commands',$ext_obj->getConsoleCommands());

        // register events subscribers        
        $this->registerEventsSubscribers();

        // add to extensions db table
        $extension = Model::Extensions();              
        $details->set('status',1);
        if ($extension->isInstalled($extension_name) == false) {            
            $extension->create($details->toArray());
        } else {
            $extension = $extension->where('name','=',$extension_name)->first();
            $extension->update($details->toArray());
        }

        // trigger core.extension.after.install event
        Arikaim::event()->trigger('core.extension.after.install',$details->toArray());
        $ext_obj->onAfterInstall();
    
        return true;
    }

    public function unInstall() 
    {
        // clear extension cache
        Arikaim::cache()->deleteExtensionItems();

        $details = $this->getProperties(true);
        $extension_name = $this->getName();

        $extension = Model::Extensions();
        $ext_obj = Factory::createExtension($extension_name,$details->get('class'));
    
        // trigger core.extension.before.uninstall event
        Arikaim::event()->trigger('core.extension.before.uninstall',$details->toArray());

        // on before unInstall event handler
        if (is_object($ext_obj) == true) {
            $ext_obj->onBeforeUnInstall();
        }
        
        // delete registered routes
        Model::Routes()->deleteExtensionRoutes($extension_name);

        // delete registered events
        Model::Events()->deleteEvents($extension_name);
        // delete registered events subscribers
        Model::EventSubscribers()->deleteSubscribers($extension_name);

        // delete extension options
        Arikaim::options()->removeExtensionOptions($extension_name);
        $result = $extension->where('name','=',$extension_name)->delete();

        // delete jobs 
        //Arikaim::jobs()->deleteExtensionJobs($extension_name);
    
        // on after unInstall event handler
        if (is_object($ext_obj) == true) {
            $ext_obj->onAfterUnInstall();
        }
        // trigger core.extension.after.uninstall event
        Arikaim::event()->trigger('core.extension.after.uninstall',$details->toArray());
        return $result;
    }

    protected function setStatus($status)
    {
        $extension = Model::Extensions()->where('name','=',$this->getName())->first();       
        return $extension->update(['status' => $status]);  
    }

    public function enable() 
    {
        // clear extension cache
        Arikaim::cache()->deleteExtensionItems();

        $name = $this->getName();
        $result = Model::Extensions()->enable($name);
        // enable extension routes
        Model::Routes()->enableExtensionRoutes($name);
        // enable extension events
        Model::Events()->enableExtensionEvents($name);   
        return (bool)$result;
    }

    public function disable() 
    {
        // clear extension cache
        Arikaim::cache()->deleteExtensionItems();
        
        $name = $this->getName();
        $result = Model::Extensions()->disable($name);
        // disable extension routes
        Model::Routes()->disableExtensionRoutes($name);        
        // disable extension events
        Model::Events()->disableExtensionEvents($name);
        return (bool)$result; 
    }   

    public function registerEventsSubscribers()
    {
        $count = 0;
        $name = $this->getName();
        $path = Path::getExtensionEventsPath($name);       
        if (File::exists($path) == false) {
            return $count;
        }

        foreach (new \DirectoryIterator($path) as $file) {
            if (($file->isDot() == true) || ($file->isDir() == true)) continue;
            if ($file->getExtension() != 'php') continue;
            
            $base_class = str_replace(".php","",$file->getFilename());
            // add event subscriber to db table
            $result = Arikaim::event()->registerSubscriber($base_class,$name);
            $count += ($result == true) ? 1 : 0;
        }     
        return $count;
    }

    public static function getTypeId($type_name)
    {
        return array_search($type_name,Self::TYPE_NAME);      
    }
}
