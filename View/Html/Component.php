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
use Arikaim\Core\Form\Properties;
use Arikaim\Core\Extension\ExtensionsManager;
use Arikaim\Core\View\Html\Template;
use Arikaim\Core\View\Html\HtmlComponent;

class Component extends HtmlComponent
{
    public function __construct() {
        parent::__construct();
        $this->setRootPath("components");
    }

    public function fetch($full_component_name, $vars = []) 
    {
        $twig = Arikaim::view()->getEnvironment();
        return $this->render($twig,$full_component_name,$vars);
    }
    
    private function render(\Twig_Environment $twig, $full_component_name, $vars = []) 
    {    
        $component_name = $this->parseName($full_component_name);
        $this->extension_name = $component_name['extension_name'];
        $type = $component_name['type'];
        $component_path = $component_name['path'];

        $template_file_name = $this->getComponentFile($component_path,"html",$type);
      
        if ($template_file_name != false) {
            if ($type == HtmlComponent::EXTENSION) {
                $path = ExtensionsManager::getExtensionViewPath($this->extension_name);
                $twig->getLoader()->addPath($path); 
            }
            // render html component code
            $this->addComponentFiles($component_path,$type);
            $this->loadProperties($full_component_name,$vars);
            // include  required components
            $this->includeRequiredComponents($component_path,$type,$vars);
            $params = $this->components->getProperties($full_component_name); 
            $component_code = $twig->render($template_file_name,$params);
          //  $component_code = $this->addComments($full_component_name,$component_code);  
            return $component_code;
        } 

        $template_file_name = $this->getComponentFile($component_path,"js",$type);
    
        if ($template_file_name != false) {
            $this->addComponentFiles($component_path,$type);
            return "";
        }        
        return Arikaim::getError("TEMPLATE_COMPONENT_NOT_FOUND",["full_component_name" => $component_path]);
    }

    private function includeRequiredComponents($full_component_name, $type, $vars = []) 
    {
        $params = $this->components->getProperties($full_component_name);
        if (empty($params['requires']['components']) == true) {
            return false;
        } 
        foreach ($params['requires']['components'] as $name) {
            $this->fetch($name,$type,$vars);
        }
    }

    public function getComponentFile($component_path,$file_ext = "html",$type) 
    {
        $parts = explode('/',$component_path);
        $component_name = end($parts);

        $template_file_name = false;
        $full_file_name = $this->getPath($component_path,$type,true) . DIRECTORY_SEPARATOR . "$component_name." . $file_ext;

        if (File::exists($full_file_name) == true) {
            $template_file_name = $this->getPath($component_path,$type,false) . DIRECTORY_SEPARATOR . "$component_name." . $file_ext;
        } else {
            $full_file_name = $this->getPath($component_name,$type,true) . DIRECTORY_SEPARATOR . "$component_name." . $file_ext;
            if (File::exists($full_file_name) == true) {
                $template_file_name = $this->getPath($component_name,$type,false) . DIRECTORY_SEPARATOR . "$component_name." . $file_ext;
            } else {
                $full_file_name = $this->getPath($component_name,HtmlComponent::SYSTEM,true) . DIRECTORY_SEPARATOR . "$component_name." . $file_ext;
                if (File::exists($full_file_name) == true) {
                    $template_file_name = $this->getPath($component_name,HtmlComponent::SYSTEM,false) . DIRECTORY_SEPARATOR . "$component_name." . $file_ext;
                }
            }
        }
        return $template_file_name;
    }

    public function loadComponent(\Twig_Environment $twig, $full_component_name, $vars = []) 
    {
        return $this->render($twig,$full_component_name,$vars);
    }

    public static function readProperties($full_component_name,$path = null)
    {
        $component = new Self;
        $name = $component->parseName($full_component_name);     
        $properties = $component->loadComponentProperties($name['path'],$name['type']); 
        if ($path != null) {
            return Utils::arrayGetValue($properties,$path);           
        }
        return $properties;
    }

    public function loadProperties($full_component_name, $vars = null)
    {            
        $name = $this->parseName($full_component_name);        
        $properties = $this->loadComponentProperties($name['path'],$name['type']); 
       
        if (is_array($properties) == true) {    
            $params = Utils::arrayMerge($properties,$this->components->getProperties($full_component_name));
            if (is_array($vars) == true) {
                $params = Utils::arrayMerge($params,$vars);
            }
            $this->components->set($full_component_name,$params);          
            return true;
        }    
        return false;
    }

    public function getCompoenentJSFile($component_path, $type)
    {
        $parts = explode('/',$component_path);
        $component_name = end($parts);
        $file_url = $this->getUrl($component_path,$type) . "/" . "$component_name.js";
        $file_name = $this->getPath($component_path,$type,true) . DIRECTORY_SEPARATOR . "$component_name.js";
    
        if (File::exists($file_name) == true) {
            return $file_url;
        } else {
            $file_url = $this->getUrl($component_name,$type) . "/" . "$component_name.js";
            $file_name = $this->getPath($component_name,$type,true) . DIRECTORY_SEPARATOR . "$component_name.js";
            if (File::exists($file_name) == true) {
               return $file_url;
            }
        }
        return false;
    }

    public function getCompoenentCSSFile($component_path, $type)
    {
        $parts = explode('/',$component_path);
        $component_name = end($parts);

        $file_url = $this->getUrl($component_path,$type) . "/" . "$component_name.css";
        $file_name = $this->getPath($component_path,$type,true) . DIRECTORY_SEPARATOR . "$component_name.css";
        if (File::exists($file_name) == true) {
           return $file_url;
        }
        return false;
    }

    public function addComments($component_path, $component_code) 
    {    
        if (Arikaim::config('debug') == true) {
            $start_comment = "<!-- template component '$component_path' -->\n";
            $end_comment = "\n<!-- end '$component_path' -->";
            $code = $start_comment . $component_code . $end_comment;
            return $code;
        }
        return $component_code;
    }   

    public function getComponentProperties($component_name)
    {
        $name = $this->parseName($component_name);
        return $this->loadComponentProperties($name['path'],$name['type']);

    }
}
