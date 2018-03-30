<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Extension;

use Arikaim\Core\FileSystem\File;
use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Form\Properties;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Db\Schema;
use Arikaim\Core\View\TemplatesManager;

class ExtensionsManager 
{
    public function __construct() 
    {
    }

    public function getExtensions($status = null, $type = null)
    {       
        $status = ($status != 1) ? 0 : 1;
        $extensions = Model::Extensions();
        if ($status != null) {
            $extensions = $extensions->where('status','=',$status);
        }
        if ($type !== null) {          
            $extensions = $extensions->where('type','=',$type);
        }
        $extensions = $extensions->orderBy('admin_link_position'); 
        $extensions = $extensions->get();
        if (is_object($extensions) == false) {
            return [];
        }
        return $extensions->toArray();
    }

    public function enable($extension_name)
    {
        $extension = Model::Extensions()->where('name','=',$extension_name)->first();       
        $extension->status = 1;
        $extension->update();  
        // enable extension routes
        Model::Routes()->enableExtensionRoutes($extension_name);
        // enable extension events
        Model::Events()->enableExtensionEvents($extension_name);       
    }

    public function disable($extension_name)
    {
        $extension = Model::Extensions()->where('name','=',$extension_name)->first();       
        $extension->status = 0;
        $extension->update();  
        // disable extension routes
        Model::Routes()->disableExtensionRoutes($extension_name);        
        // disable extension events
        Model::Events()->disableExtensionEvents($extension_name);
    }

    public function deleteRoutes($extension_name)
    {
        $routes = Model::Routes();
        return $routes->deleteExtensionRoutes($extension_name);
    }

    public function install($extension_name, $update = false) 
    {          
        $details = $this->getExtensionProperties($extension_name);
      
        $ext_obj = Factory::createExtension($extension_name,$details['class']);
        if (is_object($ext_obj) == false) {
            Arikaim::errors()->addError("EXTENSION_CLASS_NOT_VALID");
            return false;
        }
        // extension before install handler
         if ($update != true) {
            // trigger core.extension.before.install event
            Arikaim::event()->trigger('core.extension.before.install',$details);
            $ext_obj->onBeforeInstall();
        }
        // create db tables 
        $db_tables = $this->getExtensionDatabaseModels($extension_name,true);
        
        // delete extension routes
        $this->deleteRoutes($extension_name);
        // delete jobs 
        Arikaim::jobs()->deleteExtensionJobs($extension_name);
        // delete registered events
        Model::Events()->deleteEvents($extension_name);         
        // delete registered events subscribers
        Model::EventSubscribers()->deleteSubscribers($extension_name);

        // run install extension
        $ext_obj->install();
        // register events subscribers 
        $this->registerEventsSubscribers($extension_name);


        // add to extensions db table
        $extension = Model::Extensions();       
       
        $details['status'] = 1;
        if ($extension->isInstalled($extension_name) == false) {
            $extension->fill($details);
            $extension->save();
        } else {
            $extension = $extension->where('name','=',$extension_name)->first();
            $info['uuid'] = $extension->uuid;
            $extension->update($details);
        }
        // after install handler
        if ($update != true) {
            // trigger core.extension.after.install event
            Arikaim::event()->trigger('core.extension.after.install',$details);
            $ext_obj->onAfterInstall();
        } else {
            // trigger core.extension.update event
            Arikaim::event()->trigger('core.extension.update',$details);
        }
        return true;
    }
    
    public function unInstall($extension_name) 
    {
        $extension = Model::Extensions();
        $details = $this->getExtensionDetails($extension_name);  
      
        $ext_obj = Factory::createExtension($extension_name,$details['class']);
        if (is_object($ext_obj) == false) {
            Arikaim::errors()->addError("EXTENSION_CLASS_NOT_VALID");
            return false;
        }
        // trigger core.extension.before.uninstall event
        Arikaim::event()->trigger('core.extension.before.uninstall',$details);

        // on before unInstall event handler
        $ext_obj->onBeforeUnInstall();
        
        // delete registered routes
        $this->deleteRoutes($extension_name);

        // delete registered events
        Model::Events()->deleteEvents($extension_name);
        // delete registered events subscribers
        Model::EventSubscribers()->deleteSubscribers($extension_name);

        // delete extension options
        Arikaim::options()->removeExtensionOptions($extension_name);
        $result = $extension->where('name','=',$extension_name)->delete();

        // delete jobs 
        Arikaim::jobs()->deleteExtensionJobs($extension_name);
    
        // on after unInstall event handler
        $ext_obj->onAfterUnInstall();

        // trigger core.extension.after.uninstall event
        Arikaim::event()->trigger('core.extension.after.uninstall',$details);
        return $result;
    }

    public function update($extension_name) 
    {
        return $this->install($extension_name,true);      
    }
    
    public function scan() 
    {
        $path = Self::getExtensionsPath();
        $result = [];
        if (File::exists($path) == false) {
            return $result;
        }

        foreach (new \DirectoryIterator($path) as $file) {
            if ($file->isDot() == true) continue;
            if ($file->isDir() == true) {
                $extension_name = $file->getFilename();            
                array_push($result,$extension_name);
            }
        }   
        return $result;
    }

    public function getExtensionPropertiesFileName($extension_name)
    {
        $file_name = strtolower($extension_name) . ".json";
        $properties_file = Self::getExtensionPath($extension_name) . DIRECTORY_SEPARATOR . $file_name;
        return $properties_file;
    }

    public function readExtensionProperties($extension_name)
    {
        $properties_file = $this->getExtensionPropertiesFileName($extension_name);
        $properties = new Properties($properties_file);
        return $properties;
    }

    public function getExtensionProperties($extension_name) 
    {       
        $properties = $this->readExtensionProperties($extension_name);
     
        $default_class_name = ucfirst($extension_name);
        $base_class_name = $properties->get('class',$default_class_name);
        try {
            $ext_obj = Factory::createExtension($extension_name,$base_class_name);
        } catch(\Exceptin $e) {
            $ext_obj = null;
        }
        if (is_object($ext_obj) == false) {
            $info['error'] = Arikaim::errors()->getError("EXTENSION_CLASS_NOT_VALID");
        }
       
        // get extension info
        $extension = Model::Extensions();
        $info['name'] = $extension_name;        
        $info['installed'] = $extension->isInstalled($extension_name);       
        $info['status'] = $extension->getStatus($extension_name);

        $properties_file = $this->getExtensionPropertiesFileName($extension_name);
        if (File::exists($properties_file) == false) {
            $info['error'] = Arikaim::errors()->getError("FILE_NOT_FOUND",['file_name' => $properties_file]);
        }
    
        $info['class'] = $base_class_name;
        $type_name = $properties->get('type','user'); 
        $info['type'] = $extension->getTypeId($type_name);
        $info['description'] = $properties->get('description');
        $info['short_description'] = $properties->get('short_description');
        $info['version'] = $properties->get('version','1.0');
        $info['title'] = $properties->get('title');  
        $info['icon'] = $properties->get('icon');         
        // admin link info     
        $info['admin_link_title'] = $properties->getByPath('admin/link/content');   
        $info['admin_link_icon'] = $properties->getByPath('admin/link/icon');   
        $info['admin_link_sub_title'] = $properties->getByPath('admin/link/sub_title');   
        $info['admin_link_component'] = $properties->getByPath('admin/link/component');   
        $info['admin_link_position'] = $properties->getByPath('admin/link/position');   

        return $info;
    }

    public function getExtensionDetails($extension_name)
    {
        if (empty($extension_name) == true) return null;
        $properties = $this->readExtensionProperties($extension_name);

        $default_class_name = ucfirst($extension_name);
        $base_class_name = $properties->get('class',$default_class_name);
        $ext_obj = Factory::createExtension($extension_name,$base_class_name);      
        if (is_object($ext_obj) == false) {
            $details['error'] = Arikaim::errors()->getError("EXTENSION_CLASS_NOT_VALID");
        } 
        $routes = Model::Routes()->getRoutes(null,$extension_name);
        
        $details['path'] = Self::getExtensionPath($extension_name);
        $details['properties'] = $properties->toArray();
        $details['class'] = $base_class_name;
        $details['routes'] = $routes;
        $details['extension_name'] = $extension_name;        
        $details['events'] = Model::Events()->getEvents($extension_name);
        $details['subscribers'] = Model::EventSubscribers()->getExtensionSubscribers($extension_name);
        $details['database'] = $this->getExtensionDatabaseModels($extension_name);
        $details['components'] = $this->getExtensionTemplateComponents($extension_name); 
        $details['pages'] = $this->getExtensionTemplatePages($extension_name); 
        $details['macros'] = $this->getExtensionTemplateMacros($extension_name); 
        
        $condition = Model::createCondition('extension_name','=',$extension_name)->toArray();
        $details['jobs'] = Arikaim::jobs()->getStorage()->getRecuringJobs($condition,true);

        return $details;
    }

    public function registerEventsSubscribers($extension_name)
    {
        $count = 0;
        $path = Self::getExtensionEventsPath($extension_name);
        if (File::exists($path) == false) {
            return $count;
        }

        foreach (new \DirectoryIterator($path) as $file) {
            if (($file->isDot() == true) || ($file->isDir() == true)) continue;
            if ($file->getExtension() != 'php') continue;
            
            $file_name = $file->getFilename();
            $base_class = str_replace(".php","",$file_name);
            $result = Arikaim::event()->registerSubscriber($base_class,$extension_name);
            $count += ($result == true) ? 1:0;
        }     
        return $count;
    }

    public function getExtensionDatabaseModels($extension_name, $install = false)
    {      
        $result = [];
        $path = Self::getExtensionModelsSchemaPath($extension_name);
        if (File::exists($path) == false) {
            return $result;
        }

        foreach (new \DirectoryIterator($path) as $file) {
            if (($file->isDot() == true) || ($file->isDir() == true)) continue;
            if ($file->getExtension() != 'php') continue;

            $file_name = $file->getFilename();
            $base_class = str_replace(".php","",$file_name);
            $model_obj = Schema::createExtensionModelSchema($extension_name,$base_class);

            if (is_subclass_of($model_obj,'Arikaim\Core\Db\Schema') == true ) {               
                $item['name'] = $model_obj->getTableName();
                if ($install == true) {
                    $item['created'] = (string)Schema::install($base_class,$extension_name);   
                }
                array_push($result,$item);
            }
        }     
        return $result;
    }

    public function getExtensionTemplateComponents($extension_name, $parent_path = null)
    {
        $result = [];
        if (empty($extension_name) == true) {
            return $result;
        }
        return TemplatesManager::getComponents($extension_name,Self::getExtensionComponentsPath($extension_name));
    }

    public function getExtensionTemplatePages($extension_name)
    {
        $result = [];
        $path = Self::getExtensionPagesPath($extension_name);
        if (File::exists($path) == false) {
            return $result;
        }

        foreach (new \DirectoryIterator($path) as $file) {
            if ($file->isDot() == true) continue;
            if ($file->isDir() == true) {
                $item['name'] = $file->getFilename();
                array_push($result,$item);
            }
        }      
        return $result;
    }

    public function getExtensionTemplateMacros($extension_name)
    {
        $result = [];
        $path = Self::getExtensionMacrosPath($extension_name);
        if (File::exists($path) == false) {
            return $result;
        }

        foreach (new \DirectoryIterator($path) as $file) {
            if (($file->isDot() == true) || ($file->isDir() == true)) continue;
            $extension = $file->getExtension();
            if (($extension == "html") || ($extension == "htm")) {
                $item['name'] = str_replace(".$extension","",$file->getFilename());
                array_push($result,$item);
            }
        }      
        return $result;
    }

    public static function getExtensionViewUrl($extension_name)
    {
        return join('/',array(Arikaim::getBaseUrl(),'arikaim','extensions',$extension_name,'view'));
    }
    
    public static function getExtensionsPath() 
    {
        return join(DIRECTORY_SEPARATOR,array(Arikaim::getRootPath(),trim(Arikaim::getBasePath(),DIRECTORY_SEPARATOR), 'arikaim','extensions')) . DIRECTORY_SEPARATOR;
    }
    
    public static function getExtensionPath($extension_name)   
    {
        return Self::getExtensionsPath() . $extension_name;
    }

    public static function getExtensionEventsPath($extension_name)   
    {
        return Self::getExtensionPath($extension_name) . DIRECTORY_SEPARATOR . 'events' . DIRECTORY_SEPARATOR;
    }

    public static function getExtensionComponentsPath($extension_name)  
    {
        return Self::getExtensionViewPath($extension_name)  . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR;
    }

    public static function getExtensionComponentPath($extension_name,$component_name)  
    {
        return Self::getExtensionComponentsPath($extension_name) . $component_name;
    }

    public static function getExtensionViewPath($extension_name)
    {
        return Self::getExtensionPath($extension_name) . DIRECTORY_SEPARATOR . "view";
    }

    public static function getExtensionMacrosPath($extension_name)
    {
        return Self::getExtensionViewPath($extension_name) . DIRECTORY_SEPARATOR . "macros" . DIRECTORY_SEPARATOR;
    }

    public static function getExtensionPagesPath($extension_name)  
    {
        return Self::getExtensionViewPath($extension_name)  . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR;
    }

    public static function getExtensionPagePath($extension_name, $page_name)  
    {
        return Self::getExtensionPagesPath($extension_name) . $page_name;
    }

    public static function getExtensionModelsPath($extension_name)   
    {
        return Self::getExtensionPath($extension_name) . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR;
    }

    public static function getExtensionModelsSchemaPath($extension_name)   
    {
        return Self::getExtensionModelsPath($extension_name) . 'schema' . DIRECTORY_SEPARATOR;
    }
}
