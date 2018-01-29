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

use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Utils\File;
use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Form\Properties;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Db\Schema;
use Arikaim\Core\Models\Routes;
//use Arikaim\Core\Models\Events;
//use Arikaim\Core\Events\EventsManager;

class ExtensionsManager 
{
    public function __construct() 
    {
    }

    public function getExtensions($status = 1)
    {
        $status = ($status != 1) ? 0 : 1;
        $extensions = Model::Extensions()->where('status','=',1)->get();
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
        $details = $this->getExtensionDetails($extension_name);  
        $ext_obj = Factory::createExtension($extension_name,$details['class']);
        if (is_object($ext_obj) == false) {
            Arikaim::errors()->addError("EXTENSION_CLASS_NOT_VALID");
            return false;
        }
        // extension before install handler
        if ($update != true) {
            $ext_obj->onBeforeInstall();
        }
        // create db tables 
        $db_tables = $this->getExtensionDatabaseModels($extension_name,true);
        
        // remove extension routes
        $this->deleteRoutes($extension_name);
        // run install extension
        $ext_obj->install();

         // delete registered events subscribers
         Model::EventsSubscribers()->deleteSubscribers($extension_name);
        // register events subscribers 
        $this->registerEventsSubscribers($extension_name);


        // add to extensions db table
        $extension = Model::Extensions();       
        $info = $this->getExtensionProperties($extension_name);
        $info['status'] = 1;
        if ($extension->isInstalled($extension_name) == false) {
            $extension->fill($info);
            $extension->save();
        } else {
            $extension = $extension->where('name','=',$extension_name)->first();
            $info['uuid'] = $extension->uuid;
            $extension->update($info);
        }
        // after install handler
        if ($update != true) {
            $ext_obj->onAfterInstall();
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
        // on before unInstall event handler
        $ext_obj->onBeforeUnInstall();
        
        // delete registered routes
        Model::Routes()->where('extension_name',$extension_name)->delete();

        // delete registered events
        Model::Events()->deleteEvents($extension_name);
        // delete registered events subscribers
        Model::EventsSubscribers()->deleteSubscribers($extension_name);

        // delete extension options
        Arikaim::options()->removeExtensionOptions($extension_name);

        $result = $extension->where('name','=',$extension_name)->delete();

        // on after unInstall event handler
        $ext_obj->onAfterUnInstall();

        return $result;
    }

    public function update($extension_name) 
    {
        return $this->install($extension_name,true);      
    }
    
    public function scan() 
    {
        $extensions_path = Self::getExtensionsPath();
        $items = [];
        $i = 1;
        foreach (new \DirectoryIterator($extensions_path) as $file) {
            if ($file->isDot() == true) continue;
            if ($file->isDir() == true) {
                $extension_name = $file->getFilename();            
                array_push($items,$extension_name);
            }
        }   
        return $items;
    }

    public function getExtensionPropertiesFileName($extension_name)
    {
        $file_name = strtolower($extension_name) . ".json";
        $properties_file = Self::getExtensionPath($extension_name) . DIRECTORY_SEPARATOR . $file_name;
        return $properties_file;
    }

    public function getExtensionPropeties($extension_name)
    {
        $properties_file = $this->getExtensionPropertiesFileName($extension_name);
        $properties = new Properties($properties_file,"extension");
        return $properties;
    }

    public function getExtensionProperties($extension_name) 
    {       
        $properties = $this->getExtensionPropeties($extension_name);
     
        $default_class_name = ucfirst($extension_name);
        $base_class_name = $properties->get('class',$default_class_name);
        $ext_obj = Factory::createExtension($extension_name,$base_class_name);
        if (is_object($ext_obj) == false) {
            $info['error'] = Arikaim::errors()->getError("EXTENSION_CLASS_NOT_VALID");
        }
       
        // get extension info
        $extension = Model::Extensions();
        $info['name'] = $extension_name;        
        $info['installed'] = $extension->isInstalled($extension_name);       
        $info['status'] = $extension->getStatus($extension_name);

        if (is_subclass_of($ext_obj,Factory::getFullInterfaceName("DefinitionInterface")) == true) {   
            // get info from extension class      
            $info['class'] = $base_class_name;
            $info['title'] = $ext_obj->getTitle();
            $info['icon'] = $ext_obj->getIcon();
            $info['version'] = $ext_obj->getVersion();
            $info['description'] = $ext_obj->getDescription();
            $info['short_description'] = $ext_obj->getShortDescription();           
        } else {
            $properties_file = $this->getExtensionPropertiesFileName($extension_name);
            if (File::exists($properties_file) == false) {
                $info['error'] = Arikaim::errors()->getError("FILE_NOT_FOUND",['file_name' => $properties_file]);
            }
            // get from json file
            $info['class'] = $base_class_name;
            $info['description'] = $properties->get('description');
            $info['short_description'] = $properties->get('short_description');
            $info['version'] = $properties->get('version','1.0');
            $info['title'] = $properties->get('title');  
            $info['icon'] = $properties->get('icon');         
        }
        // admin link info 
        if (is_subclass_of($ext_obj,Factory::getFullInterfaceName("AdminMenuLinkInterface")) == true) {   
            $info['admin_link_title'] = $ext_obj->getLinkTitle();
            $info['admin_link_icon'] = $ext_obj->getLinkIcon();  
            $info['admin_link_sub_title'] = $ext_obj->getLinkSubtitle();
            $info['admin_link_component'] = $ext_obj->getLinkComponentName();
        } else { 
            $properties_file = $this->getExtensionPropertiesFileName($extension_name);
            if (File::exists($properties_file) == false) {
                $info['error'] = Arikaim::errors()->getError("FILE_NOT_FOUND",['file_name' => $properties_file]);
            }   
             // get from json file  
            $info['admin_link_title'] = $properties->getByPath('admin/link/content');   
            $info['admin_link_icon'] = $properties->getByPath('admin/link/icon');   
            $info['admin_link_sub_title'] = $properties->getByPath('admin/link/sub_title');   
            $info['admin_link_component'] = $properties->getByPath('admin/link/component');   
        }
        return $info;
    }

    public function getExtensionDetails($extension_name)
    {
        if (empty($extension_name) == true) return null;
        $properties = $this->getExtensionPropeties($extension_name);

        $default_class_name = ucfirst($extension_name);
        $base_class_name = $properties->get('class',$default_class_name);
        $ext_obj = Factory::createExtension($extension_name,$base_class_name);      
        if (is_object($ext_obj) == false) {
            $details['error'] = Arikaim::errors()->getError("EXTENSION_CLASS_NOT_VALID");
        } 
        $routes = Model::Routes()->getRoutes(null,$extension_name);

        $details['class'] = $base_class_name;
        $details['routes'] = $routes;
        $details['extension_name'] = $extension_name;
        $details['events_subscribers'] = Model::EventsSubscribers()->getSubscribers($extension_name);
        $details['events'] = Model::Events()->getEvents($extension_name);
        $details['database'] = $this->getExtensionDatabaseModels($extension_name);
        $details['components'] = $this->getExtensionTemplateComponents($extension_name); 
        $details['pages'] = $this->getExtensionTemplatePages($extension_name); 
        $details['macros'] = $this->getExtensionTemplateMacros($extension_name);         
        return $details;
    }

    public function registerEventsSubscribers($extension_name)
    {
        $count = 0;
        if (empty($extension_name) == true) {
            return false;
        }
        $path = Self::getExtensionEventsPath($extension_name);
       
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
        if (empty($extension_name) == true) return $result;
        $path = Self::getExtensionModelsSchemaPath($extension_name);

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

    public function getExtensionTemplateComponents($extension_name)
    {
        $result = [];
        if (empty($extension_name) == true) {
            return $result;
        }
        $path =  Self::getExtensionComponentsPath($extension_name);

        foreach (new \DirectoryIterator($path) as $file) {
            if ($file->isDot() == true) continue;
            if ($file->isDir() == true) {
                $item['name'] = $file->getFilename();
                array_push($result,$item);
            }
        }      
        return $result;
    }

    public function getExtensionTemplatePages($extension_name)
    {
        $result = [];
        if (empty($extension_name) == true) {
            return $result;
        }
        $path = Self::getExtensionPagesPath($extension_name);
     
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
        if (empty($extension_name) == true) {
            return $result;
        }
        $path = Self::getExtensionMacrosPath($extension_name);
      
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
        return Self::getExtensionViewPath($extension_name)  . 'components' . DIRECTORY_SEPARATOR;
    }

    public static function getExtensionComponentPath($extension_name,$component_name)  
    {
        return Self::getExtensionComponentsPath($extension_name) . $component_name;
    }

    public static function getExtensionViewPath($extension_name)
    {
        return Self::getExtensionPath($extension_name) . DIRECTORY_SEPARATOR . "view" . DIRECTORY_SEPARATOR;
    }

    public static function getExtensionMacrosPath($extension_name)
    {
        return Self::getExtensionViewPath($extension_name) . "macros" . DIRECTORY_SEPARATOR;
    }

    public static function getExtensionPagesPath($extension_name)  
    {
        return Self::getExtensionViewPath($extension_name)  . 'pages' . DIRECTORY_SEPARATOR;
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
