<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\View\Html;

use Arikaim\Core\Arikaim;
use Arikaim\Core\FileSystem\File;
use Arikaim\Core\Utils\Arrays;
use Arikaim\Core\View\Template;
use Arikaim\Core\Form\Properties;
use Arikaim\Core\Access\Access;
use Arikaim\Core\Utils\Mobile;
use Arikaim\Core\View\Html\Component;

class BaseComponent   
{
    protected $options;
    protected $options_file_name;
  
    public function __construct() 
    {
        $this->setOptionsFileName("component.json");    
    }

    public function fetch($component, $params = [])
    {
        if (empty($component->getTemplateFile()) == true) {
            return $component;
        }
       
        $code = Arikaim::view()->fetch($component->getTemplateFile(),$params);
        if ($component->getOption('add-comments') == true) {
            $code = $this->addComments($component->getPath(),$component->getHtmlCode());
        }  

        $component->setHtmlCode($code);         
        return $component;
    }

    public function getPropertiesFileName($component)
    {
        $language = $component->getLanguage();
        $language_code = ($language != "en") ? "-". $language : "";

        $file_name = $this->getComponentFile($component,"json",$language_code);

        if ($file_name === false) {
            $file_name = $this->getComponentFile($component,"json");
            if ($file_name === false) {
                return false;
            }
        } 
        return $component->getFullPath() . $file_name;   
    }

    public function loadComponentProperties($component)
    {
        $file_name = $component->getPropertiesFileName();
        $properties = new Properties($file_name,null,Template::getVars());                 
        return $properties;
    }

    public function getPath($component, $full = true, $relative_path = null) 
    {
        if ($full == true) {
            $template_name = Template::getTemplatePath($component->getTemplateName(),$component->getType());
        } else {
            if ($component->getType() != Template::EXTENSION) {
                $template_name = $component->getTemplateName();
            } else {
                $template_name = "";
            }
        }
        if (empty($relative_path) == true) {
            $path = $component->getPath();
        } else {
            $path = $relative_path;
        }
        $path = $template_name . DIRECTORY_SEPARATOR . $component->getRootPath() . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR;
        return $path;   
    }
  
    protected function setOptionsFileName($file_name)
    {
        $this->options_file_name = $file_name;
    }

    public function getParentPath($path)
    {
        if (empty($path) == true) {
            return false;
        }
        $parent_path = dirname($path);
        if ($parent_path == "." || empty($path) == true) {
            return false;
        }
        return $parent_path; 
    }

    public function getOptionsFileName($component, $parent_path = null)
    {   
        if (empty($parent_path) == true) {
            $path = $component->getFullpath();
            $parent_path = $component->getPath();
        } else {
            $path = $this->getPath($component,true,$parent_path);
        }

        $file_name = $path . $this->options_file_name;
       
        if (File::exists($file_name) == false) {
            $parent_path = $this->getParentPath($parent_path);             
            if ($parent_path != false) {
                return $this->getOptionsFileName($component,$parent_path);               
            }
            return false;
        }
        return $file_name;
    }

    protected function loadOptions($component)
    {
        $file_name = $component->getOptionsFileName();
        $options = new Properties($file_name,'');         
        return $options;
    }

    public function processOptions($component)
    {
        $error = false;       
        // check auth access 
        $auth = $component->getOption('access/auth');
        $access = Arikaim::access()->checkAccess($auth);   
        if ($access == false) {
           $error = Arikaim::errors()->getError("ACCESS_DENIED");
        }
        // check permissions
        $permissions = $component->getOption('access/permissions');
        if (is_array($permissions) == true) {
            foreach ($permissions as $name => $permission) {        
                if (empty($permission) == false) {                            
                    if (Arikaim::access()->hasPermission($name,$permission) == false) {
                        $error = Arikaim::errors()->getError("ACCESS_DENIED");
                    }
                }            
            }
        }    
        // inlcude js files
        $source_component_name = $component->getOption('include/js');
        if (empty($source_component_name) == false) {
            $files = $this->getComponentFiles($source_component_name,"js");
            $component->addFiles($files,"js");
        }
        // include css files 
        $source_component_name = $component->getOption('include/css');
        if (empty($source_component_name) == false) {
            $files = $this->getComponentFiles($source_component_name,"css");
            $component->addFiles($files,"css");
        }
        // mobile only option
        $mobile_only = $component->getOption('mobile-only');      
        if ($mobile_only == "true") {
            if (Mobile::mobile() == false) {    
               $component->clearContent();               
            }
        }
        
        if ($error !== false) {
            $error = Arikaim::getError("TEMPLATE_COMPONENT_ERROR",["full_component_name" => $component->getName(),'details' => $error]);
            $component->setError($error);
        }
        return $component;
    }

    public function getComponentFiles($component_name, $file_type = "js")
    {
        $component = $this->create($component_name,'components');
        $files = $component->getFiles($file_type);      
        return $files;
    }

    public function create($name, $root_path, $language = null)
    {
        $component = new Component($name,$root_path,$language);
      
        $component->setFullPath($this->getPath($component,true));  
        $component->setFilePath($this->getPath($component,false)); 
        
        $file_name = $this->getOptionsFileName($component);
        $component->setOptionsFileName($file_name);

        // js file
        $file = $this->addComponentFile($component,'js');
        $component->addFile($file,'js');

        // css file
        $file = $this->addComponentFile($component,'css');
        $component->addFile($file,'css');

        // html file
        $file = $this->addComponentFile($component,'html');
        $component->addFile($file,'html');

        // properties
        $file_name = $this->getPropertiesFileName($component);       
        $component->setPropertiesFileName($file_name);
  
        if ($component->isValid() == false) {
            $component->setError(Arikaim::getError("TEMPLATE_COMPONENT_NOT_FOUND",["full_component_name" => $name]));
            return $component;
        }
       
        $component->setOptions($this->loadOptions($component)->toArray());
     
        $component->setProperties($this->loadComponentProperties($component)->toArray());
        $component->setHtmlCode("");

        $component = $this->processOptions($component);            
        return $component;
    }

    public function getFileUrl($component,$file_name)
    {
        $template_url = Template::getTemplateUrl($component->getTemplateName(),$component->getType());
        return $template_url . '/' . str_replace(DIRECTORY_SEPARATOR,'/',$component->getRootPath() . '/'. $component->getPath()) . '/' . $file_name;
    }

    public function addComponentFile($component,$file_ext)
    {
        $file_name = $this->getComponentFile($component,$file_ext);
        if ($file_name === false) {
            return false;
        }

        $file['file_name'] = $file_name;
        $file['path'] = $this->getPath($component,false); 
        $file['full_path'] = $this->getPath($component,true);   
        $file['url'] = $this->getFileUrl($component,$file_name);
        return $file;
    }

    public function getComponentFile($component, $file_ext = "html", $language_code = "") 
    {         
        $file_name = $component->getName() . $language_code . "." . $file_ext;
        $full_file_name = $component->getFullPath() . DIRECTORY_SEPARATOR . $file_name;
        return File::exists($full_file_name) ? $file_name : false;
    }
}
