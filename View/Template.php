<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\View;

use Arikaim\Core\FileSystem\File;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Arikaim;
use Arikaim\Core\View\UiLibrary;
use Arikaim\Core\View\Theme;
use Arikaim\Core\Form\Properties;
use Arikaim\Core\Extension\ExtensionsManager;
use Arikaim\Core\Db\Model;

/**
 * Html Template
*/
class Template
{
    const USER      = 1;
    const SYSTEM    = 2;
    const EXTENSION = 3;

    const SYSTEM_TEMPLATE_NAME = 'system';
 
    public function __construct() 
    {
    }

    public function loadProperties($template_name) 
    {         
        $template_path = Self::getTemplatePath($template_name); 
        $full_file_name = $template_path . DIRECTORY_SEPARATOR . "$template_name" . ".json";
        $properties = new Properties($full_file_name);                 
        return $properties;
    }

    public function includeJsFiles($properties, $type)
    {
        $js_files = $properties->getByPath("include/js",[]);   
        if (is_array($js_files) == false) {
            return false;
        }  
        $url = Self::getTemplateUrl($type);  
        foreach ($js_files as $file_name) {
            $file_url = $url . "/js/" . $file_name; 
            Arikaim::page()->properties()->add('template.js.files',$file_url);
        }
        return true;
    }

    public function includeCssFiles($properties, $type)
    {
        $css_files = $properties->getByPath("include/css",[]);     
        if (is_array($css_files) == false) {
            return false;
        }
        $url = Self::getTemplateUrl($type);  
        foreach ($css_files as $file_name) {
            $file_url = $url . "/css/" . $file_name; 
            Arikaim::page()->properties()->add('template.css.files',$file_url);
        }
        return true;
    }

    public function includeLibraryFiles($properties)
    {   
        $frameworks = [];
        $library = new UiLibrary();
        $library_files = $properties->getByPath("include/library",[]);  
    
        if (is_array($library_files) == false) {
            $library_files = [];
        }
        $include_lib = [];
        foreach ($library_files as $library_name) {
            $files = $library->getFiles($library_name);
            $params = $library->getParams($library_name);
            foreach($files as $file) {
                $item['file'] = UiLibrary::getLibraryFileUrl($library_name,$file);
                $item['type'] = File::getExtension(UiLibrary::getLibraryFilePath($library_name,$file));
                $item['params'] = $params;
                $item['library'] = $library_name;
                array_push($include_lib,$item);
            }           
            if ($library->isFramework($params) == true) {
                array_push($frameworks,$library_name);
            }
        }
        Arikaim::page()->properties()->set('ui.library.files',$include_lib);
        $this->setIncludedLibraries($library_files);
        $this->setFrameworks($frameworks);
        return true;
    }

    public function includeThemeFiles($properties, $template_name, $type)
    {
        $default_theme = $properties->get("default-theme",null);
        $current_theme = Theme::getCurrentTheme($template_name,$default_theme);

        $library = $properties->getByPath("themes/$current_theme/library","");
        // get theme from other template
        $template = $properties->getByPath("themes/$current_theme/template","");
        if (empty($template) == false) {
            $template_name = $template;
        }
        if(empty($library) == false) {
            // load theme from library           
            $file_url = UiLibrary::getThemeFileUrl($library,$current_theme);
        } else {
            // load from template
            $file = Theme::getThemeFile($properties,$current_theme);
            $file_url = $this->getThemeFileUrl($template_name,$current_theme,$file);
        }
        if ($file_url != false) {
            $theme['name'] = $current_theme;
            $theme['file'] = $file;
            Arikaim::page()->properties()->add('template.theme',$file_url);
            return true;
        }
        return false;
    }

    public function getThemeFileUrl($template_name, $theme_name, $theme_file)
    {
        $path = Theme::getTemplateThemePath($template_name,$theme_name);
        $full_file_name = $path . DIRECTORY_SEPARATOR . $theme_file;
    
        if (File::exists($full_file_name) == false) {
            return false;
        }
        $url = Theme::getTemplateThemeUrl($template_name,$theme_name);
        $file_url = $url . $theme_file;
        return $file_url;
    }

    public static function includeFiles($type) 
    {
        $self = new Self();

        $template_name = Self::getTemplateName($type);
        $properties = $self->loadProperties($template_name);
        
        // include javascript files
        $self->includeJsFiles($properties,$type);
        // include css files
        $self->includeCssFiles($properties,$type);
        // include ui lib files
        $self->includeLibraryFiles($properties);  
        // include theme files 
        $self->includeThemeFiles($properties,$template_name,$type);  
        // set loader component
        $loader = $properties->get('loader',false);
        Arikaim::session()->set("template.loader",$loader);

        return true;
    }

    public static function getJsFiles()
    {
        return Arikaim::page()->properties()->get('template.js.files');
    }

    public static function getCssFiles()
    {
        return Arikaim::page()->properties()->get('template.css.files');
    }

    public static function getThemeFiles()
    {
        return Arikaim::page()->properties()->get('template.theme');
    }
    
    public static function getLibraryFiles()
    {
        $files = Arikaim::page()->properties()->get('ui.library.files');
        if (is_array($files) == false) {
            return [];
        }
        return $files;
    }

    public function setIncludedLibraries(array $libraries)    
    {
        $libs = json_encode($libraries);
        Arikaim::session()->set("ui.included.libraries",$libs);
    }

    public function setFrameworks(array $frameworks)
    {
        $frameworks = json_encode($frameworks);
        Arikaim::session()->set("ui.included.frameworks",$frameworks);
    }

    public static function getLibraries()    
    {
        return Arikaim::session()->get("ui.included.libraries");
    }
    
    public static function getFrameworks()    
    {
        return Arikaim::session()->get("ui.included.frameworks");
    }

    public static function getVars()
    {
        $base_path = Arikaim::getBasePath(); 
        $base_url  = Arikaim::getBaseUrl();
        return array(
            'base_path'             => $base_path,
            'base_url'              => $base_url,
            'template_url'          => Self::getTemplateUrl(),
            'current_template_name' => Self::getTemplateName(),
            'ui_path'               => $base_path . Arikaim::getViewPath(),
            'system_template_url'   => Self::getTemplateUrl(Self::SYSTEM_TEMPLATE_NAME),
            'system_template_name'  => Self::SYSTEM_TEMPLATE_NAME,
            'ui_library_path'       => $base_path . DIRECTORY_SEPARATOR . Arikaim::getViewPath() . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR,
            'ui_library_url'        => Arikaim::getViewURL() . "/library/"            
        );
    }

    public static function getTemplatesPath()
    {
        return Arikaim::getViewPath() . DIRECTORY_SEPARATOR . 'templates';
    }

    public static function getSystemMacrosPath()
    {
        return Self::getMacrosPath(Self::SYSTEM_TEMPLATE_NAME);
    }

    public static function getMacrosPath($template_name = null)
    {
        return Self::getTemplatePath($template_name) . DIRECTORY_SEPARATOR . "macros" . DIRECTORY_SEPARATOR;
    }

    public static function getMacroPath($macro_name, $template_name = null)
    {
        if (empty($template_name) == true) {
            $template_name = Self::getTemplateName();
        }
        return DIRECTORY_SEPARATOR . $template_name . DIRECTORY_SEPARATOR . "macros" . DIRECTORY_SEPARATOR . $macro_name;
    }

    public static function getExtensionMacroPath($macro_name, $extension_name)
    {
        return ExtensionsManager::getExtensionMacrosPath($extension_name,false) . $macro_name;       
    }

    public static function getSystemMacroPath($macro_name)
    {
        return Self::getMacroPath($macro_name,Self::SYSTEM_TEMPLATE_NAME);
    }

    public static function getComponentsPath($template_name = null)
    {
        return Self::getTemplatePath($template_name) . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR; 
    }

    public static function getPagesPath($template_name = null)
    {
        return Self::getTemplatePath($template_name) . DIRECTORY_SEPARATOR . "pages" . DIRECTORY_SEPARATOR; 
    }

    public static function getTemplateName($type = null)     
    {    
        if ($type == Self::SYSTEM) {
            return Self::SYSTEM_TEMPLATE_NAME;
        }
        try {            
            if (is_object(Arikaim::options()) == false) {
                return "default";
            } 
        } catch(\Exception $e) {
            return "default";
        }
        return Arikaim::options()->get('current.template',"default");     
    }
    
    public static function getTemplateUrl($template_name = null, $type = null) 
    {       
        if ($type == Self::EXTENSION) {
            return ExtensionsManager::getExtensionViewUrl($template_name);
        }
        if ($type == Self::SYSTEM) {
            $template_name = Self::SYSTEM_TEMPLATE_NAME;
        }
        if ($template_name == Self::SYSTEM) {
            $template_name = Self::SYSTEM_TEMPLATE_NAME;              
        }
        if (($template_name == null) || (is_numeric($template_name) == true))  {           
            $template_name = Template::getTemplateName();
        } 
        return Arikaim::getViewURL() . "/templates/$template_name";       
    }

    public static function getTemplatePath($template_name = null, $type = null) 
    {   
        if ($type == Self::EXTENSION) {
            return ExtensionsManager::getExtensionViewPath($template_name);
        }
        
        if (empty($template_name) == true)  {           
            $template_name = Template::getTemplateName($type);
        } 
        return Arikaim::getViewPath() . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $template_name;       
    }

    public static function getLanguage() 
    {  
        $language = Arikaim::session()->get('language');
        if ($language == null) {
            $language = Arikaim::cookies()->get('language');     
        }
        if (($language == "") || ($language == null)) { 
            try {
                $language = Model::Language()->getDefaultLanguage();
            } catch(\Exception $e) {
                $language = Arikaim::config('settings/defaultLanguage');
                if (empty($language) == true) {
                    $language = "en";
                }   
            }           
        }            
        return $language;
    }
    
    /**
     * Set current language
     *
     * @param string $language_code Language code
     * @return string
     */
    public static function setLanguage($language_code) 
    {
        Arikaim::session()->set('language',$language_code);
        Arikaim::cookies()->set('language',$language_code);
        return $language_code;
    }

    /**
     * Set current front end framework.
     *
     * @param string $library_name UI library name
     * @return void
     */
    public static function setCurrentFramework($library_name)
    {
        Arikaim::session()->set("current.framework",$library_name);
    }

    /**
     * Return current front end framework used in page
     *
     * @return string
     */
    public static function getCurrentFramework()
    {
        $framework = Arikaim::session()->get("current.framework");
        if (empty($framework) == true) {
            $frameworks = json_decode(Self::getFrameworks());
            $framework = last($frameworks);
            Self::setCurrentFramework($framework);
        }
        return $framework;
    }
}
