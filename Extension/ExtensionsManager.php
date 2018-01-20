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
use Arikaim\Core\Models\Events;
use Arikaim\Core\Events\EventsManager;

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

    public function install($extension_name, $update = false) 
    {       
        $extension = Model::Extensions();          
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
       
        // register routes
        $routes = Model::Routes();
        $added_routes = 0;
        $routes->where('extension_name',$extension_name)->delete();
        foreach($details['routes'] as $key => $item) {
            $item['extension_name'] = $extension_name;
            $item['uuid'] = Utils::getUUID();
            $result = Routes::create($item); 
            if ($result == true) $added_routes++;
        }

        // register events       
        $events = Model::Events();
        $added_events = 0;
        $events->where('extension_name',$extension_name)->delete();
        foreach($details['events'] as $key => $item) {
            $item['extension_name'] = $extension_name;
            $item['uuid'] = Utils::getUUID();
            $result = Events::create($item); 
            if ($result == true) $added_events++;
        }

        // add to extensions table
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
        
        // remove registered routes
        Model::Routes()->where('extension_name',$extension_name)->delete();
        // remove registered event handlers
        Model::Events()->where('extension_name',$extension_name)->delete();
        // remove extension options
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

    public function createExtnsion($extension_name)
    {

    }

    public function getExtensionProperties($extension_name) 
    {       
        $properties_file = Self::getExtensionPath($extension_name) . DIRECTORY_SEPARATOR . "$extension_name.json"; 
        $properties = new Properties($properties_file,"extension");
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

        if ($ext_obj instanceof Arikaim\Core\Interfaces\DefinitionInterface == true) {   
            // get info from extension class      
            $info['class'] = $base_class_name;
            $info['title'] = $ext_obj->getTitle();
            $info['icon'] = $ext_obj->getIcon();
            $info['version'] = $ext_obj->getVersion();
            $info['description'] = $ext_obj->getDescription();
            $info['short_description'] = $ext_obj->getShortDescription();           
        } else {
            // get from json file
            $info['class'] = $base_class_name;
            $info['description'] = $properties->get('description');
            $info['short_description'] = $properties->get('short_description');
            $info['version'] = $properties->get('version');
            $info['title'] = $properties->get('title');  
            $info['icon'] = $properties->get('icon');         
        }
        // admin link info 
        if ($ext_obj instanceof Arikaim\Core\Interfaces\AdminMenuLinkInterface == true) {   
            $info['admin_link_title'] = $ext_obj->getLinkTitle();
            $info['admin_link_icon'] = $ext_obj->getLinkIcon();  
            $info['admin_link_sub_title'] = $ext_obj->getLinkSubtitle();
            $info['admin_link_component'] = $ext_obj->getLinkComponentName();
        } else {      
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

        $properties_file = Self::getExtensionPath($extension_name) . DIRECTORY_SEPARATOR . "extension.json"; 
        $properties = new Properties($properties_file,"extension");
        $default_class_name = ucfirst($extension_name);
        $base_class_name = $properties->get('class',$default_class_name);
        $ext_obj = Factory::createExtension($extension_name,$base_class_name);      
        if (is_object($ext_obj) == false) {
            $details['error'] = Arikaim::errors()->getError("EXTENSION_CLASS_NOT_VALID");
            $routes = [];
        } else {
            $routes = $ext_obj->getRoutes();
        }
        $details['class'] = $base_class_name;
        $details['routes'] = is_object($routes) ? $routes->toArray() : [];
        $details['extension_name'] = $extension_name;
        $details['events'] = $this->getExtensionEvents($extension_name);
        $details['database'] = $this->getExtensionDatabaseModels($extension_name);
        $details['components'] = $this->getExtensionTemplateComponents($extension_name); 
        $details['pages'] = $this->getExtensionTemplatePages($extension_name); 
        $details['macros'] = $this->getExtensionTemplateMacros($extension_name);         
        return $details;
    }

    public function getExtensionEvents($extension_name)
    {
        $result = [];
        if (empty($extension_name) == true) return $result;

        $extension_path = Self::getExtensionPath($extension_name);
        $path = join(DIRECTORY_SEPARATOR,array($extension_path,'events'));

        foreach (new \DirectoryIterator($path) as $file) {
            if (($file->isDot() == true) || ($file->isDir() == true)) continue;
            if ($file->getExtension() != 'php') continue;
            
            $file_name = $file->getFilename();
            $base_class = str_replace(".php","",$file_name);
            $event = EventsManager::createEvent($base_class,$extension_name);
            if ($event != false) {               
                $item['name'] = $event->getName();
                $item['title'] = $event->getTitle();
                $item['handler_class'] = $event->getClassName();                
                $item['priority'] = $event->getPriority();
                array_push($result,$item);
            }
        }     
        return $result;
    }

    public function getExtensionDatabaseModels($extension_name, $install = false)
    {      
        $result = [];
        if (empty($extension_name) == true) return $result;

        $extension_path = Self::getExtensionPath($extension_name);
        $path = join(DIRECTORY_SEPARATOR,array($extension_path,'models','schema'));

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
        if (empty($extension_name) == true) return $result;

        $extension_path = Self::getExtensionPath($extension_name);
        $path = join(DIRECTORY_SEPARATOR,array($extension_path,'view','components'));
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
        if (empty($extension_name) == true) return $result;

        $extension_path = Self::getExtensionPath($extension_name);
        $path = join(DIRECTORY_SEPARATOR,array($extension_path,'view','pages'));
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
        if (empty($extension_name) == true) return $result;
        
        $extension_path = Self::getExtensionPath($extension_name);
        $path = join(DIRECTORY_SEPARATOR,array($extension_path,'view','macros'));
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

    public static function getExtensionsPath() 
    {
        return join(DIRECTORY_SEPARATOR, array(Arikaim::getRootPath(),trim(Arikaim::getBasePath(),DIRECTORY_SEPARATOR), 'arikaim','extensions')) . DIRECTORY_SEPARATOR;
    }
    
    public static function getExtensionPath($extension_name)   
    {
        return Self::getExtensionsPath() . $extension_name;
    }

    public static function getExtensionComponentPath($extension_name,$component_name)  
    {
        return join(DIRECTORY_SEPARATOR, array(Self::getExtensionPath($extension_name),'view','components',$component_name));
       
    }

    public static function getExtensionViewPath($extension_name)
    {
        return Self::getExtensionPath($extension_name) . DIRECTORY_SEPARATOR . "view" . DIRECTORY_SEPARATOR;
    }

    public static function getExtensionViewUrl($extension_name)
    {
        return join('/',array(Arikaim::getBaseUrl(),'arikaim','extensions',$extension_name,'view'));
    }

    public static function getExtensionPagePath($extension_name, $page_name)  
    {
        return  $path = join(DIRECTORY_SEPARATOR,array('arikaim','extensions',$extension_name,'view','pages',$page_name));
    }
}
