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
use Arikaim\Core\Utils\File;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\View\Html\Template;
use Arikaim\Core\Form\Properties;
use Arikaim\Core\Extension\ExtensionsManager;

class HtmlComponent   
{
    const TEMPLATE  = 1;
    const SYSTEM    = 2;
    const EXTENSION = 3;

    protected $components; 
    protected $extension_name;
    protected $root_path;
    protected $include_js_files_key;
    protected $include_css_files_key;

    public function __construct() 
    {
        $this->components = Arikaim::templateComponents();
        $this->extension_name = null;      
    }

    public function renderCode($template_code, $vars = [])
    {
        $view = Arikaim::view(); 
        $code = " {% import 'system/macros/countries.html' as countries %} ";
        $code .= $template_code;
        $result = $view->fetchFromString($code,$vars);
        return $result; 
    }

    public function getPropertiesFileName($path, $component_name, $check_exists = false)
    {
        $language = Arikaim::getLanguage();
        if ($language == "en") {
            $code = "";
        } else {
            $code = "-$language";
        }
        $default_file_name = $path . DIRECTORY_SEPARATOR . $component_name . ".json";  
        $file_name = $path . DIRECTORY_SEPARATOR . $component_name . $code . ".json";   
        if ($check_exists == true) {
            if (File::exists($file_name) == false) {
                return $default_file_name;
            } 
        }  
        return $file_name;   
    }

    public function parseName($component_name)
    {
        $result['template_name'] = "";
        $result['extension_name'] = ""; 
        $name_parts = explode(':',$component_name);

        if (isset($name_parts[1]) == false) {                                
            $result['path'] = str_replace('.','/',$name_parts[0]);
            $result['type'] = Component::TEMPLATE;
        } else {
            $result['extension_name'] = $name_parts[0];
            $result['path'] = str_replace('.','/',$name_parts[1]);
            $result['type'] = Component::EXTENSION;
        }

        if ($result['extension_name'] == Template::SYSTEM_TEMPLATE_NAME) {
            $result['template_name'] = Template::SYSTEM_TEMPLATE_NAME;
            $result['extension_name'] = "";
            $result['type'] = Component::SYSTEM;            
        }       
        return $result;
    }    

    public function loadComponentProperties($component_path, $type)
    {
        $parts = explode('/',$component_path);
        $component_name = end($parts);
        $full_file_name = $this->getPropertiesFileName($this->getPath($component_path,$type,true),$component_name,true);  
        $properties = new Properties($full_file_name,'component',Arikaim::getTemplateVars());                 
        return $properties->toArray();
    }

    public function getPath($component_name, $type, $full = true) 
    {
        $parts = explode('/',$component_name);
        $component_name = Utils::arrayToPath($parts);
    
        if ($full == true) {
            switch ($type) {
                case HtmlComponent::TEMPLATE : {
                    $path = join(DIRECTORY_SEPARATOR,array(Self::getTemplatePath(),$this->root_path,$component_name));
                    break;
                }
                case HtmlComponent::SYSTEM : {  
                    $path = join(DIRECTORY_SEPARATOR,array(Self::getTemplatePath(Template::SYSTEM_TEMPLATE_NAME),$this->root_path,$component_name));
                    break;
                }  
                case HtmlComponent::EXTENSION : {                        
                    $path = ExtensionsManager::getExtensionComponentPath($this->extension_name,$component_name);
                    break;
                }  
            }    
        } else {
            $path = join(DIRECTORY_SEPARATOR,array($this->root_path,$component_name));
        }
        return $path;   
    }

    public function getUrl($component_name, $type) 
    {
        $parts = explode('/',$component_name);
        $component_name = Utils::arrayToPath($parts);
        switch ($type) {
            case HtmlComponent::TEMPLATE : {             
                $url = join('/',array(Self::getTemplateURL(),$this->root_path,$component_name));
                break;
            }
            case HtmlComponent::SYSTEM : {
                $url = join('/',array(Self::getTemplateURL(Template::SYSTEM_TEMPLATE_NAME),$this->root_path,$component_name));
                break;
            }
            case HtmlComponent::EXTENSION : {
                $url = join('/',array(ExtensionsManager::getExtensionViewUrl($this->extension_name),$this->root_path,$component_name));
            }
        }       
        return $url;   
    }

    public function addComponentFiles($component_path, $type) 
    {
        // js file
        $js_file = $this->getCompoenentJSFile($component_path,$type);
        if ($js_file != false) {
            Arikaim::page('properties')->add('include.components.js',$js_file);
            $this->components->addIncludeFile("js_files",$js_file);
        }
        // css file
        $css_file = $this->getCompoenentCSSFile($component_path,$type);
        if ($css_file != false) {
            Arikaim::page('properties')->add('include.components.css',$css_file);
            $this->components->addIncludeFile("css_files",$css_file);
        }
    }

    public function getComponentsJSFiles()
    {
        return Arikaim::page('properties')->get('include.components.js');
    }

    public function getComponentsCSSFiles()
    {
        return Arikaim::page('properties')->get('include.components.css');
    }
    
    protected function setRootPath($path) 
    {
        $this->root_path = $path;
    }
    
    public static function getTemplatePath($template_name = null) 
    {   
        if ($template_name == null)  {           
            $template_name = Self::getTemplateName();
        } 
        return Arikaim::getViewPath() . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $template_name;       
    }

    public static function getTemplatesPath()
    {
        return Arikaim::getViewPath() . DIRECTORY_SEPARATOR . 'templates';
    }

    public static function getTemplateURL($template_name = null) 
    {
        if ($template_name == null)  {           
            $template_name = Self::getTemplateName();
        } 
        return Arikaim::getViewURL() . "/templates/$template_name";       
    }

    public static function getTemplateName() 
    {    
        try {            
            if (is_object(Arikaim::options()) == false) {
                return "default";
            } 
        } catch(\Exception $e) {
            return "default";
        }
        return Arikaim::options()->get('current.template',"default");     
    }

    public function readSettingsFile($file_name,$args = [])
    {

    }
}
