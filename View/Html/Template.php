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

use Arikaim\Core\Controler;
use Arikaim\Core\Utils\File;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Arikaim;
use Arikaim\Core\View\Html\HtmlComponent;
use Arikaim\Core\View\UiLibrary;

class Template extends HtmlComponent
{
    const USER     = 1;
    const SYSTEM   = 2;
    const SYSTEM_TEMPLATE_NAME = 'system';
    
    protected $current_page;
   
    public function __construct() 
    {
     
    }

    public function loadParams($template_name, $type = Template::USER) 
    {         
        if ($type == Template::USER) {
            $template_path = Self::getTemplatePath(); 
        } else {
            $template_path = Self::getTemplatePath(Template::SYSTEM_TEMPLATE_NAME);           
        } 
        $full_file_name = $this->getPropertiesFileName($template_path,$template_name,true);    
        $data = File::loadJSONFile($full_file_name);
        if (is_array($data['template']) == true) {
            return $data['template'];
        }
        return [];
    }

    public function includeTemplateJsFiles($properties)
    {
        if (isset($properties['include']['js']) == false) return false;
        foreach ($properties['include']['js'] as $key => $file_name) {
            Arikaim::page('properties')->add('template.js.files',$file_name);
        }
        return true;
    }

    public function includeTemplateCSSFiles($properties)
    {
        if (isset($properties['include']['css']) == false) return false;
        foreach ($properties['include']['css'] as $key => $file_name) {
            Arikaim::page('properties')->add('template.css.files',$file_name);
        }
        return true;
    }

    public function includeTemplateLibraryFiles($properties)
    {
        $library = new UiLibrary();
        if (isset($properties['include']['library']) == false) return false;
        $include_lib = [];
        foreach ($properties['include']['library'] as $name) {
            $files = $library->getFiles($name);
            foreach($files as $file) {
                $item['name'] = $name;
                $item['file'] = $file;
                $item['type'] = File::getExtension(UiLibrary::getLibraryFilePath($file));
                array_push($include_lib,$item);
            }           
        }
        Arikaim::page('properties')->set('ui.library.files',$include_lib);
        $this->setIncludedLibraries($properties['include']['library']);
        return true;
    }

    public function includeTemplateThemeFiles($properties)
    {
        $default_theme = $this->getDefaultTheme($properties);
        if (isset($properties['themes']) == true) {
            $theme_file = $properties['themes']['items'][$default_theme]['file'];
        }
        if (isset($theme_file) == true) {
            $theme['name'] = $default_theme;
            $theme['file'] = $theme_file;
            Arikaim::page('properties')->add('template.theme',$theme);
        }
    }

    public function includeTemplateFiles($type = Template::USER) 
    {

        switch ($type) {
            case HtmlComponent::TEMPLATE: {                
                $template_name = Self::getTemplateName();
                break;
            }
            case HtmlComponent::SYSTEM: {              
                $template_name = Self::SYSTEM_TEMPLATE_NAME;
                break;
            }
            case HtmlComponent::EXTENSION: {              
                $template_name = Self::getTemplateName();
                break;
            }
        }

        $properties = $this->loadParams($template_name,$type);    

        if (isset($properties['include']) == true) {            
            // include javascript files
            $this->includeTemplateJsFiles($properties);
            // include css files
            $this->includeTemplateCSSFiles($properties);
            // include ui lib files
            $this->includeTemplateLibraryFiles($properties);        
        }
        // include theme files
        $this->includeTemplateThemeFiles($properties);        
    }

    public function getDefaultTheme($properties) 
    {        
        if (isset($properties['themes']['default']) == true) {
            return $properties['themes']['default'];
        }
        return "";
    }

    public function getSupportedLanguages($template_name = null) 
    {
        if ($template_name == null) {
            $template_name = Self::getTemplateName();
        }
    }

    public function getTemplateJSFiles()
    {
        return Arikaim::page('properties')->get('template.js.files');
    }

    public function getTemplateCSSFiles()
    {
        return Arikaim::page('properties')->get('template.css.files');
    }

    public function getTheme()
    {
        return Arikaim::page('properties')->get('template.theme');
    }

    public function getLibraryFiles()
    {
        $files = Arikaim::page('properties')->get('ui.library.files');
        if (is_array($files) == false) {
            return [];
        }
        return $files;
    }

    public function setIncludedLibraries($libs_array)    
    {
        $libs = Utils::jsonEncode($libs_array);
        Arikaim::session()->set("ui.included.libraries",$libs);
    }

    public static function getIncludedLibraries()    
    {
        $libs = Arikaim::session()->get("ui.included.libraries");
        return json_decode($libs);
    }
}
