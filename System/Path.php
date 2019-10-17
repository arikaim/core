<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System;

use Arikaim\Core\View\Html\Component;
use Arikaim\Core\View\Template\Template;
use Arikaim\Core\View\Theme;

/**
 * All path constants and helpers
 */
class Path 
{
    const ARIKAIM_VIEW_PATH = ARIKAIM_PATH . DIRECTORY_SEPARATOR . 'view';
    const ARIKAIM_BIN_PATH  = ARIKAIM_PATH . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR;
    const EXTENSIONS_PATH   = ARIKAIM_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR;  
    const MODULES_PATH      = ARIKAIM_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR;
    const LIBRARY_PATH      = Self::ARIKAIM_VIEW_PATH . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR;
    const TEMPLATES_PATH    = Self::ARIKAIM_VIEW_PATH . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
    const COMPONENTS_PATH   = Self::ARIKAIM_VIEW_PATH . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR;
    const CONFIG_PATH       = ARIKAIM_PATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;
    const CACHE_PATH        = ARIKAIM_PATH . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
    const VIEW_CACHE_PATH   = Self::CACHE_PATH . 'views' . DIRECTORY_SEPARATOR;
    const LOGS_PATH         = ARIKAIM_PATH . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
    const STORAGE_PATH      = ARIKAIM_PATH . DIRECTORY_SEPARATOR . "storage" . DIRECTORY_SEPARATOR;
    const STORAGE_TEMP_PATH = Self::STORAGE_PATH . 'temp' . DIRECTORY_SEPARATOR;
  
    const CORE_NAMESPACE           = "Arikaim\\Core";
    const MODULES_NAMESAPCE        = "Arikaim\\Modules";
    const MIDDLEWARE_NAMESPACE     = Self::CORE_NAMESPACE . "\\Middleware";
    const ACCESS_NAMESPACE         = Self::CORE_NAMESPACE . "\\Access";
    const EXTENSIONS_NAMESPACE     = "Arikaim\\Extensions";
    const CONTROLLERS_NAMESPACE    = Self::CORE_NAMESPACE . "\\Controllers";
    const API_CONTROLLERS_NAMESPACE = Self::CONTROLLERS_NAMESPACE . "\\Api";
    const INTERFACES_NAMESPACE     = Self::CORE_NAMESPACE . "\\Interfaces";

    /**
     * Get module path
     *
     * @param string $name
     * @return string
     */
    public static function getModulePath($name)
    {
        return Self::MODULES_PATH . $name . DIRECTORY_SEPARATOR;
    }

    /**
     * Gte module console commands path
     *
     * @param string $name
     * @return string
     */
    public static function getModuleConsolePath($name)
    {
        return Self::MODULES_PATH . $name . DIRECTORY_SEPARATOR . 'console' . DIRECTORY_SEPARATOR;
    }

    /**
     * Get macro path
     *
     * @param string $macro_name
     * @param string $template_name
     * @return string
     */
    public static function getMacroPath($macro_name, $template_name = null)
    {
        $template_name = (empty($template_name) == true) ? Template::getTemplateName() : $template_name;          
        return DIRECTORY_SEPARATOR . $template_name . DIRECTORY_SEPARATOR . "macros" . DIRECTORY_SEPARATOR . $macro_name;
    }

    /**
     * Get system macro path
     *
     * @param string $macro_name
     * @return string
     */
    public static function getSystemMacroPath($macro_name)
    {
        return Self::getMacroPath($macro_name,Template::SYSTEM_TEMPLATE_NAME);
    }

    /**
     * Get template theme path
     *
     * @param string $template_name
     * @param string $theme_name
     * @return string
     */
    public static function getTemplateThemePath($template_name, $theme_name = Theme::DEFAULT_THEME_NAME)
    {
        return Self::getTemplateThemesPath($template_name) . DIRECTORY_SEPARATOR . $theme_name;
    }

    /**
     * Get template themes path
     *
     * @param string $template_name
     * @return string
     */
    public static function getTemplateThemesPath($template_name)
    {
        return Self::getTemplatePath($template_name) . "themes";
    }

    /**
     * Get library theme path
     *
     * @param string $library_name
     * @param string $theme_name
     * @return string
     */
    public static function getLibraryThemePath($library_name, $theme_name = Theme::DEFAULT_THEME_NAME)
    {
        return Self::getLibraryThemesPath($library_name) . DIRECTORY_SEPARATOR . $theme_name;
    }

    /**
     * Get library themes path
     *
     * @param string $library_name   
     * @return string
     */
    public static function getLibraryThemesPath($library_name)
    {
        return Self::getLibraryPath($library_name) . DIRECTORY_SEPARATOR . "themes";
    }

    /**
     * Get components path
     *
     * @param string $template_name
     * @param string $type
     * @return string
     */
    public static function getComponentsPath($template_name = null, $type = null)
    {
        return Self::getTemplatePath($template_name,$type) . "components" . DIRECTORY_SEPARATOR; 
    }

    /**
     * Get extension macro path
     *
     * @param string $macro_name
     * @param string $extension_name
     * @return string
     */
    public static function getExtensionMacroPath($macro_name, $extension_name)
    {
        return Self::getExtensionMacrosRelativePath($extension_name) . $macro_name;       
    }

    /**
     * Get macros path
     *
     * @param string|null $template_name
     * @param string $type
     * @return string
     */
    public static function getMacrosPath($template_name = null, $type = null)
    {
        return Self::getTemplatePath($template_name,$type) . "macros" . DIRECTORY_SEPARATOR;
    }

    /**
     * Get pages path
     *
     * @param string $template_name
     * @param string $type
     * @return string
     */
    public static function getPagesPath($template_name = null, $type = null)
    {
        return Self::getTemplatePath($template_name,$type) . "pages" . DIRECTORY_SEPARATOR; 
    }

    /**
     * Get template path
     *
     * @param string $template_name
     * @param string $type
     * @return string
     */
    public static function getTemplatePath($template_name = null, $type = null) 
    {   
        switch($type) {
            case Component::EXTENSION_COMPONENT:
                return Self::getExtensionViewPath($template_name);
            case Component::TEMPLATE_COMPONENT:
                return  Self::TEMPLATES_PATH . $template_name . DIRECTORY_SEPARATOR;
            case Component::GLOBAL_COMPONENT:
                return Path::ARIKAIM_VIEW_PATH . DIRECTORY_SEPARATOR;
        }                     
    }

    /**
     * Get library file path
     *
     * @param string $library_name
     * @param string $file_name
     * @return string
     */
    public static function getLibraryFilePath($library_name, $file_name) {
        return Self::getLibraryPath($library_name) . DIRECTORY_SEPARATOR . $file_name;
    }
    
    /**
     * Get library path
     *
     * @param string $library_name
     * @return string
     */
    public static function getLibraryPath($library_name)
    {
        return Self::LIBRARY_PATH . $library_name;
    }

    /**
     * Get extension view path
     *
     * @param string $extension_name
     * @return string
     */
    public static function getExtensionViewPath($extension_name)
    {
        return Path::EXTENSIONS_PATH . $extension_name . DIRECTORY_SEPARATOR . "view" . DIRECTORY_SEPARATOR;
    }

    /**
     * Get extension jobs path
     *
     * @param string $extension_name
     * @return string
     */
    public static function getExtensionJobsPath($extension_name)   
    {
        return Path::EXTENSIONS_PATH . $extension_name . DIRECTORY_SEPARATOR . 'jobs' . DIRECTORY_SEPARATOR;
    }

    /**
     * Get extension subscribers path.
     *
     * @param string $extension_name
     * @return string
     */
    public static function getExtensionSubscribersPath($extension_name)   
    {
        return Self::EXTENSIONS_PATH . $extension_name . DIRECTORY_SEPARATOR . 'subscribers' . DIRECTORY_SEPARATOR;
    }
    
    /**
     * Get extension model path
     *
     * @param string $extension_name
     * @return string
     */
    public static function getExtensionModelsPath($extension_name)   
    {
        return Self::EXTENSIONS_PATH . $extension_name . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR;
    }

    /**
     * Get extensions models schema path
     *
     * @param string $extension_name
     * @return string
     */
    public static function getExtensionModelsSchemaPath($extension_name)   
    {
        return Self::getExtensionModelsPath($extension_name) . 'schema' . DIRECTORY_SEPARATOR;
    }

    /**
     * Get extension pages path
     *
     * @param string $extension_name
     * @return string
     */
    public static function getExtensionPagesPath($extension_name)  
    {
        return Self::getExtensionViewPath($extension_name) . 'pages' . DIRECTORY_SEPARATOR;
    }

    /**
     * Get extension macros path
     *
     * @param string $extension_name
     * @return string
     */
    public static function getExtensionMacrosPath($extension_name)
    {
        return Path::getExtensionViewPath($extension_name) . "macros" . DIRECTORY_SEPARATOR;
    }

    /**
     * Get extension macros relative path
     *
     * @param string $extension_name
     * @return string
     */
    public static function getExtensionMacrosRelativePath($extension_name)
    {
        return $extension_name . DIRECTORY_SEPARATOR . "view" . DIRECTORY_SEPARATOR . "macros" . DIRECTORY_SEPARATOR;
    }

    /**
     * Get extension components path
     *
     * @param string $extension_name
     * @return string
     */
    public static function getExtensionComponentsPath($extension_name)  
    {
        return Path::getExtensionViewPath($extension_name) . 'components' . DIRECTORY_SEPARATOR;
    }

    /**
     * Get extension component path
     *
     * @param string $extension_name
     * @param string $component_name
     * @return string
     */
    public static function getExtensionComponentPath($extension_name, $component_name)  
    {
        return Self::getExtensionComponentsPath($extension_name) . $component_name;
    }

    /**
     * Get parent path
     *
     * @param string $path
     * @return string
     */
    public static function getParentPath($path)
    {
        if (empty($path) == true) {
            return false;
        }       
        $parent_path = dirname($path);

        return ($parent_path == "." || empty($path) == true) ? false : $parent_path;          
    }

    /**
     * Return current script path
     *
     * @return string
     */
    public static function getScriptPath()
    {
        return realpath(dirname(__FILE__));
    }
}
