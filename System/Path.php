<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System;

use Arikaim\Core\View\Html\Component;

class Path 
{
    const ARIKAIM_VIEW_PATH = ARIKAIM_PATH . DIRECTORY_SEPARATOR . 'view';
    const ARIKAIM_BIN_PATH  = ARIKAIM_PATH . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR;
    const EXTENSIONS_PATH   = ARIKAIM_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR;  
    const MODULES_PATH      = ARIKAIM_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR;
    const LIBRARY_PATH      = Self::ARIKAIM_VIEW_PATH . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR;
    const TEMPLATES_PATH    = Self::ARIKAIM_VIEW_PATH . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
    const CONFIG_PATH       = ARIKAIM_PATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;
    const VIEW_CACHE_PATH   = ARIKAIM_CACHE_PATH . 'views' . DIRECTORY_SEPARATOR;
    const LOGS_PATH         = ARIKAIM_PATH . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
    
    const CORE_NAMESPACE           = "Arikaim\\Core";
    const MODULES_NAMESAPCE        = "Arikaim\\Modules";
    const MIDDLEWARE_NAMESPACE     = Self::CORE_NAMESPACE . "\\Middleware";
    const EXTENSIONS_NAMESPACE     = "Arikaim\\Extensions";
    const CONTROLERS_NAMESPACE     = Self::CORE_NAMESPACE . "\\Controlers";
    const API_CONTROLERS_NAMESPACE = Self::CONTROLERS_NAMESPACE . "\\Api";
    const INTERFACES_NAMESPACE     = Self::CORE_NAMESPACE . "\\Interfaces";

    public static function getTemplateThemePath($template_name, $theme_name = 'default')
    {
        return Self::getTemplateThemesPath($template_name) . DIRECTORY_SEPARATOR . $theme_name;
    }

    public static function getTemplateThemesPath($template_name)
    {
        return Self::getTemplatePath($template_name) . DIRECTORY_SEPARATOR . "themes";
    }

    public static function getLibraryThemePath($library_name, $theme_name = 'default')
    {
        return Self::getLibraryThemesPath($library_name) . DIRECTORY_SEPARATOR . $theme_name;
    }

    public static function getLibraryThemesPath($library_name)
    {
        return Self::getLibraryPath($library_name) . DIRECTORY_SEPARATOR . "themes";
    }

    public static function getComponentsPath($template_name = null)
    {
        return Self::getTemplatePath($template_name) . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR; 
    }

    public static function getExtensionMacroPath($macro_name, $extension_name)
    {
        return Self::getExtensionMacrosRelativePath($extension_name) . $macro_name;       
    }

    public static function getMacrosPath($template_name = null)
    {
        return Self::getTemplatePath($template_name) . DIRECTORY_SEPARATOR . "macros" . DIRECTORY_SEPARATOR;
    }

    public static function getPagesPath($template_name = null)
    {
        return Self::getTemplatePath($template_name) . DIRECTORY_SEPARATOR . "pages" . DIRECTORY_SEPARATOR; 
    }

    public static function getTemplatePath($template_name = null, $type = null) 
    {   
        return ($type == Component::EXTENSION_LOCATION) ? Self::getExtensionViewPath($template_name) : Self::TEMPLATES_PATH . $template_name . DIRECTORY_SEPARATOR;               
    }

    public static function getLibraryFilePath($library_name, $file_name) {
        return Self::getLibraryPath($library_name) . DIRECTORY_SEPARATOR . $file_name;
    }

    public static function getLibraryPath($library_name)
    {
        return Self::LIBRARY_PATH . $library_name;
    }

    public static function getExtensionViewPath($extension_name)
    {
        return Path::EXTENSIONS_PATH . $extension_name . DIRECTORY_SEPARATOR . "view" . DIRECTORY_SEPARATOR;
    }

    public static function getExtensionJobsPath($extension_name)   
    {
        return Path::EXTENSIONS_PATH . $extension_name . DIRECTORY_SEPARATOR . 'jobs' . DIRECTORY_SEPARATOR;
    }

    public static function getExtensionEventsPath($extension_name)   
    {
        return Self::EXTENSIONS_PATH . $extension_name . DIRECTORY_SEPARATOR . 'events' . DIRECTORY_SEPARATOR;
    }
    
    public static function getExtensionModelsPath($extension_name)   
    {
        return Self::EXTENSIONS_PATH . $extension_name . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR;
    }

    public static function getExtensionModelsSchemaPath($extension_name)   
    {
        return Self::getExtensionModelsPath($extension_name) . 'schema' . DIRECTORY_SEPARATOR;
    }

    public static function getExtensionPagesPath($extension_name)  
    {
        return Self::getExtensionViewPath($extension_name) . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR;
    }

    public static function getExtensionMacrosPath($extension_name)
    {
        return Path::getExtensionViewPath($extension_name) . DIRECTORY_SEPARATOR . "macros" . DIRECTORY_SEPARATOR;
    }

    public static function getExtensionMacrosRelativePath($extension_name)
    {
        return $extension_name . DIRECTORY_SEPARATOR . "view" . DIRECTORY_SEPARATOR . "macros" . DIRECTORY_SEPARATOR;
    }

    public static function getExtensionComponentsPath($extension_name)  
    {
        return Path::getExtensionViewPath($extension_name) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR;
    }

    public static function getExtensionComponentPath($extension_name,$component_name)  
    {
        return Self::getExtensionComponentsPath($extension_name) . $component_name;
    }
}
