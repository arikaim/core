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

class BaseComponent   
{
    protected $root_path;
    protected $options;
    protected $options_file_name;
  
    public function __construct() 
    {
        $this->setOptionsFileName("component.json");    
    }

    public function fetch($component, $params = [])
    {
        if (empty($component['template_file']) == true) {
            return $component;
        }
       
        $component['html_code'] = Arikaim::view()->fetch($component['template_file'],$params);
        if ($this->getOption($component,'add-comments') == true) {
            $component['html_code'] = $this->addComments($component['path'],$component['html_code']);
        }           
        return $component;
    }

    public function getPropertiesFileName($component)
    {
        $language_code = ($component['language'] != "en") ? "-". $component['language']: "";
        $file_name = $this->getComponentFile($component,"json",$language_code);

        if ($file_name === false) {
            return $component['full_path'] . $this->getComponentFile($component,"json");
        } 
        return $component['full_path'] . $file_name;   
    }

    public function loadComponentProperties($component)
    {
        $file_name = "";
        if (isset($component['files']['properties']['file_name']) == true) {
            $file_name = $component['files']['properties']['file_name'];
        }
        $properties = new Properties($file_name,null,Template::getVars());                 
        return $properties;
    }

    public function getPath($component, $full = true, $relative_path = null) 
    {
        if ($full == true) {
            $template_name = Template::getTemplatePath($component['template_name'],$component['type']);
        } else {
            if ($component['type'] != Template::EXTENSION) {
                $template_name = $component['template_name'];
            } else {
                $template_name = "";
            }
        }
        if (empty($relative_path) == true) {
            $path = $component['path'];
        } else {
            $path = $relative_path;
        }
        $path = $template_name . DIRECTORY_SEPARATOR . $this->root_path . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR;
        return $path;   
    }
 
    protected function setRootPath($path) 
    {
        $this->root_path = $path;
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
            $path = $component['full_path'];
            $parent_path = $component['path'];
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
        $file_name = $component['files']['options']['file_name'];
        $options = new Properties($file_name,'');         
        return $options;
    }

    public function processOptions($component)
    {
        $result = true;       
        // check auth access 
        $auth = $this->getOption($component,'access/auth');
        $access = Arikaim::access()->checkAccess($auth);   
        if ($access == false) {
           return Arikaim::errors()->getError("ACCESS_DENIED");
        }
          
        // check permissions
        $permissions = $this->getOption($component,'access/permissions');
        if (is_array($permissions) == true) {
            foreach ($permissions as $name => $permission) {        
                if (empty($permission) == false) {                            
                    if (Arikaim::access()->hasPermission($name,$permission) == false) {
                        return Arikaim::errors()->getError("ACCESS_DENIED");
                    }
                }            
            }
        }         
        return $result;
    }

    public function getOption($component, $option_name, $default = null)
    {
        $option = Arrays::getValue($component['options'],$option_name);
        if (empty($option) && $default !== null) {
            $option = $default;
        }
        return $option;
    }

    public function getParentComponentPath($path)
    {       
        $parts = explode(DIRECTORY_SEPARATOR,$path);       
        if (last($parts) == $this->root_path) {
            return false;
        }
        return dirname($path);
    }

    public function resolve($component_name)
    {
        $component = $this->parseName($component_name);
        $component['error']     = "";
        $component['files']     = [];
        $component['root_path'] = $this->root_path;
        $component['full_path'] = $this->getPath($component,true);  
        $component['file_path'] = $this->getPath($component,false); 
        $component['language']  = Template::getLanguage();

        $component = $this->getComponentFiles($component);
    
        if ($this->isValid($component) == false) {
            $component['error'] = Arikaim::getError("TEMPLATE_COMPONENT_NOT_FOUND",["full_component_name" => $component_name]);
        }
       
        if (isset($component['files']['html']['file_name']) == true) {
            $component['template_file'] = $component['file_path'] . $component['files']['html']['file_name'];
        } else {
            $component['template_file'] = false;
        }
       
        if (isset($component['files']['options']['file_name']) == true) {
            $component['options'] = $this->loadOptions($component)->toArray();
        } else {
           // $component['files']['options'] = null;
            $component['options'] = [];
        }
        $component['properties'] = $this->loadComponentProperties($component)->toArray();
        $component['html_code'] = "";

        // process options
        $result = $this->processOptions($component);
        if ($result !== true) {
            $component['error'] = Arikaim::getError("TEMPLATE_COMPONENT_ERROR",["full_component_name" => $component_name,'details' => $result]);
        }
        return $component;
    }

    public function hasError($component)
    {
        return empty($component['error']) ? false : true;
    }

    public function getFileUrl($component,$file_name)
    {
        $template_url = Template::getTemplateUrl($component['template_name'],$component['type']);
        return $template_url . "/" . str_replace(DIRECTORY_SEPARATOR,'/',$this->root_path . '/'. $component['path']) . "/" . $file_name;
    }

    public function addComponentFile($component,$file_ext)
    {
        $file_name = $this->getComponentFile($component,$file_ext);
        if ($file_name != false) {
            $component['files'][$file_ext]['file_name'] = $file_name;
            $component['files'][$file_ext]['path'] = $this->getPath($component,false); 
            $component['files'][$file_ext]['full_path'] = $this->getPath($component,true);   
            $component['files'][$file_ext]['url'] = $this->getFileUrl($component,$file_name);
        }
        return $component;
    }

    public function getComponentFiles($component)
    {
        $file_name = $this->getOptionsFileName($component);
        if ($file_name !== false) {
            $component['files']['options']['file_name'] = $file_name;
        }
        // js file
        $component = $this->addComponentFile($component,"js");
        // css file
        $component = $this->addComponentFile($component,"css");
        // html file
        $component = $this->addComponentFile($component,"html");
        // properties
        $file_name = $this->getPropertiesFileName($component);
        if ($file_name !== false) {
            $component['files']['properties']['file_name'] = $file_name;
        }
        return $component;
    }

    public function parseName($component_name)
    {
        $name_parts = explode(':',$component_name);
        $result['template_name'] = Template::getTemplateName();
        $result['full_name'] = $component_name;

        if (isset($name_parts[1]) == false) {                                
            $result['path'] = str_replace('.','/',$name_parts[0]);            
            $result['type'] = Template::USER;
            $result['extension_name'] = ""; 
        } else {
            $result['extension_name'] = $name_parts[0];
            $result['template_name'] = $name_parts[0];
            $result['path'] = str_replace('.','/',$name_parts[1]);
            $result['type'] = Template::EXTENSION;
        }

        $parts = explode('/',$result['path']);
        $result['name'] = end($parts);

        // parse path
        $path_parts = explode('#',$result['path']);
        if (isset($path_parts[1]) == true) {
            $result['template_name'] = $path_parts[0];
            $result['path'] = $path_parts[1];
            $result['type'] = Template::USER;
        } 

        if ($result['extension_name'] == Template::SYSTEM_TEMPLATE_NAME) {
            $result['template_name'] = Template::SYSTEM_TEMPLATE_NAME;
            $result['extension_name'] = "";
            $result['type'] = Template::SYSTEM;            
        }       
        return $result;
    }   

    public function hasContent($component)
    {
        if ($component['template_file'] == false) {
            return false;
        }
        return true;
    }

    public function isValid($component)
    {
        return (count($component['files']) == 0) ? false : true;
    }

    public function getProperties($component)
    {
        if (isset($component['properties']) == true) {
            return $component['properties'];
        }
        return [];
    }

    public function getComponentFile($component, $file_ext = "html", $language_code = "") 
    {         
        $file_name = $component['name'] . $language_code . "." . $file_ext;
        $full_file_name = $component['full_path'] . DIRECTORY_SEPARATOR . $file_name;
        return File::exists($full_file_name) ? $file_name : false;
    }
}
