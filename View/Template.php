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
use Arikaim\Core\Arikaim;
use Arikaim\Core\View\Theme;
use Arikaim\Core\Db\Model;
use Arikaim\Core\System\Path;
use Arikaim\Core\System\Url;
use Arikaim\Core\Packages\Template\TemplatesManager;
use Arikaim\Core\Packages\Library\LibraryManager;

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

    public static function includeLibraryFiles(array $library_list)
    {   
        $manager = new LibraryManager();
        $frameworks = [];
        $include_lib = [];

        foreach ($library_list as $library_name) {
            $library = $manager->createPackage($library_name);
            $files = $library->getFiles();
            $params = $library->getParams();
            foreach($files as $file) {
                $item['file'] = Url::getLibraryFileUrl($library_name,$file);
                $item['type'] = File::getExtension(Path::getLibraryFilePath($library_name,$file));
                $item['params'] = $params;
                $item['library'] = $library_name;
                array_push($include_lib,$item);
            }           
            if ($library->isFramework() == true) {
                array_push($frameworks,$library_name);
            }
        }
        
        Arikaim::page()->properties()->set('ui.library.files',$include_lib);       
        Arikaim::session()->set("ui.included.libraries",json_encode($library_list));
        Arikaim::session()->set("ui.included.frameworks",json_encode($frameworks));
        return true;
    }

    public static function includeThemeFiles($template_name, $type)
    {  
        // cehck cache
        $file_url = Arikaim::cache()->fetch('template.theme.file');
        if (empty($file_url) == false) {
            Arikaim::page()->properties()->add('template.theme',$file_url);
            return true;
        }

        $manager = new TemplatesManager();
        $properties = $manager->createPackage($template_name)->getProperties();

        $manager = new LibraryManager();
        $default_theme = $properties->get("default-theme",null);
        $current_theme = Theme::getCurrentTheme($template_name,$default_theme);

        $library = $properties->getByPath("themes/$current_theme/library","");
        $library_package = $manager->createPackage($library);
        // get theme from other template
        $template = $properties->getByPath("themes/$current_theme/template","");
        $template_name = (empty($template) == false) ? $template : $template_name;
           
        if (empty($library) == false) {
            // load theme from library           
            $theme_file_name = $library_package->getThemeFile();
            $file_url = Url::getLibraryThemeFileUrl($library,$theme_file_name,$current_theme);
        } else {
            // load from template
            $file = Theme::getThemeFile($properties,$current_theme);
            $file_url = Self::getThemeFileUrl($template_name,$current_theme,$file);
        }
        if ($file_url != false) {
            $theme['name'] = $current_theme;
            $theme['file'] = $file;
            Arikaim::page()->properties()->add('template.theme',$file_url);
            // saev to cache
            Arikaim::cache()->save('template.theme.file',$file_url,3);
            return true;
        }
        return false;
    }

    public static function getThemeFileUrl($template_name, $theme_name, $theme_file)
    {
        return Self::getTemplateThemeUrl($template_name,$theme_name) . $theme_file;       
    }

    public static function includeFiles($type) 
    {
        $url = Self::getTemplateUrl($type);  
        $template_name = Self::getTemplateName($type);

        $files = Arikaim::cache()->fetch('template.files');
        if (is_array($files) == false) {
            $manager = new TemplatesManager();
            $properties = $manager->createPackage($template_name)->getProperties();
            
            $files['js'] = $properties->getByPath("include/js",[]);   
            $files['js'] = array_map(function($value) use($url) {
                return $url . "/js/" . $value; 
            },$files['js']);
    
            $files['css'] = $properties->getByPath("include/css",[]);   
            $files['css'] = array_map(function($value) use($url) {
                return $url . "/css/" . $value; 
            },$files['css']);

            $files['library'] = $properties->getByPath("include/library",[]);
            $files['loader'] = $properties->get('loader',false);
            Arikaim::cache()->save('template.files',$files,3);
        }
      
        Arikaim::page()->properties()->merge('template.js.files',$files['js']);
        Arikaim::page()->properties()->merge('template.css.files',$files['css']);

        // include ui lib files                
        Self::includeLibraryFiles($files['library']);  
        // include theme files 
        Self::includeThemeFiles($template_name,$type);  
        // set loader component
        Arikaim::session()->set("template.loader",$files['loader']);

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
        return (is_array($files) == false) ? [] : $files;
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
        $template_url = Self::getTemplateUrl();
        $template_name = Self::getTemplateName();
        $system_template_name = Self::getTemplateUrl(Self::SYSTEM_TEMPLATE_NAME);

        return [
            'base_path'             => ARIKAIM_BASE_PATH,
            'base_url'              => Url::ARIKAIM_BASE_URL,
            'template_url'          => $template_url,
            'current_template_name' => $template_name,
            'ui_path'               => ARIKAIM_BASE_PATH . Path::ARIKAIM_VIEW_PATH,
            'system_template_url'   => $system_template_name,
            'system_template_name'  => Self::SYSTEM_TEMPLATE_NAME,
            'ui_library_path'       => ARIKAIM_BASE_PATH . DIRECTORY_SEPARATOR . Path::ARIKAIM_VIEW_PATH . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR,
            'ui_library_url'        => Url::ARIKAIM_VIEW_URL . "/library/"            
        ];
    }

    public static function getSystemMacrosPath()
    {
        return Path::getMacrosPath(Self::SYSTEM_TEMPLATE_NAME);
    }

    public static function getMacroPath($macro_name, $template_name = null)
    {
        if (empty($template_name) == true) {
            $template_name = Self::getTemplateName();
        }
        return DIRECTORY_SEPARATOR . $template_name . DIRECTORY_SEPARATOR . "macros" . DIRECTORY_SEPARATOR . $macro_name;
    }

    public static function getSystemMacroPath($macro_name)
    {
        return Self::getMacroPath($macro_name,Self::SYSTEM_TEMPLATE_NAME);
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
    
    public static function getTemplateThemeUrl($template_name, $theme_name = Self::DEFAULT_THEME_NAME)
    {
        return Self::getTemplateUrl($template_name) . "/themes/$theme_name/";
    }

    public static function getTemplateUrl($template_name = null, $type = null) 
    {       
        if ($type == Self::EXTENSION) {
            return Url::getExtensionViewUrl($template_name);
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
        return Url::ARIKAIM_VIEW_URL . "/templates/$template_name";       
    }

    public static function getLanguage() 
    {  
        $language = Arikaim::session()->get('language');
        if ($language == null) {
            $language = Arikaim::cookies()->get('language');     
        }
        if (empty($language) == true) { 
            try {
                $language = Model::Language()->getDefaultLanguage();
            } catch(\Exception $e) {
                $language = Arikaim::config('settings/defaultLanguage');
                if (empty($language) == true ) {
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
        if (empty($framework) == true || $framework == null) {
            $frameworks = json_decode(Self::getFrameworks());
            $frameworks = (is_array($frameworks) == true) ? $frameworks : [];
            $framework = last($frameworks);
            Self::setCurrentFramework($framework);
        }
        return $framework;
    }

    public static function getComponents($path)
    {       
        if (File::exists($path) == false) {
            return [];
        }
        $items = [];
        $dir = new \RecursiveDirectoryIterator($path,\RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($dir,\RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $file) {
            if ($file->isDir() == true) {
                $item['name'] = $file->getFilename();   
               
                $component_path = str_replace($path,'',$file->getRealPath());                
                $component_path = str_replace(DIRECTORY_SEPARATOR,'.',$component_path);
    
                $item['full_name'] = $component_path;
                array_push($items,$item);
            }
        }
        return $items;
    }

    public static function getPages($path)
    {
        if (File::exists($path) == false) {
            return [];
        }
        $items = [];
        foreach (new \DirectoryIterator($path) as $file) {
            if ($file->isDot() == true) continue;
            if ($file->isDir() == true) {
                $item['name'] = $file->getFilename();
                array_push($items,$item);
            }
        }
        return $items;
    }

    public static function getMacros($path)
    {       
        if (File::exists($path) == false) {
            return [];
        }
        $items = [];
        foreach (new \DirectoryIterator($path) as $file) {
            if (($file->isDot() == true) || ($file->isDir() == true)) continue;
            $file_ext = $file->getExtension();
            if (($file_ext != "html") && ($file_ext != "htm")) continue;           
            
            $item['name'] = str_replace(".$file_ext",'',$file->getFilename());
            array_push($items,$item);            
        }
        return $items;
    }
}
