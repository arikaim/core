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
     * @param string $macroName
     * @param string $template
     * @return string
     */
    public static function getMacroPath($macroName, $template = null)
    {
        $template = (empty($template) == true) ? Template::getTemplateName() : $template;          
        return DIRECTORY_SEPARATOR . $template . DIRECTORY_SEPARATOR . "macros" . DIRECTORY_SEPARATOR . $macroName;
    }

    /**
     * Get system macro path
     *
     * @param string $macroName
     * @return string
     */
    public static function getSystemMacroPath($macroName)
    {
        return Self::getMacroPath($macroName,Template::SYSTEM_TEMPLATE_NAME);
    }

    /**
     * Get template theme path
     *
     * @param string $template
     * @param string $theme
     * @return string
     */
    public static function getTemplateThemePath($template, $theme = Theme::DEFAULT_THEME_NAME)
    {
        return Self::getTemplateThemesPath($template) . DIRECTORY_SEPARATOR . $theme;
    }

    /**
     * Get template themes path
     *
     * @param string $template
     * @return string
     */
    public static function getTemplateThemesPath($template)
    {
        return Self::getTemplatePath($template) . "themes";
    }

    /**
     * Get library theme path
     *
     * @param string $library
     * @param string $theme
     * @return string
     */
    public static function getLibraryThemePath($library, $theme = Theme::DEFAULT_THEME_NAME)
    {
        return Self::getLibraryThemesPath($library) . DIRECTORY_SEPARATOR . $theme;
    }

    /**
     * Get library themes path
     *
     * @param string $library   
     * @return string
     */
    public static function getLibraryThemesPath($library)
    {
        return Self::getLibraryPath($library) . DIRECTORY_SEPARATOR . "themes";
    }

    /**
     * Get components path
     *
     * @param string $template
     * @param string $type
     * @return string
     */
    public static function getComponentsPath($template = null, $type = null)
    {
        return Self::getTemplatePath($template,$type) . "components" . DIRECTORY_SEPARATOR; 
    }

    /**
     * Get extension macro path
     *
     * @param string $macroName
     * @param string $extension
     * @return string
     */
    public static function getExtensionMacroPath($macroName, $extension)
    {
        return Self::getExtensionMacrosRelativePath($extension) . $macroName;       
    }

    /**
     * Get macros path
     *
     * @param string|null $template
     * @param string $type
     * @return string
     */
    public static function getMacrosPath($template = null, $type = null)
    {
        return Self::getTemplatePath($template,$type) . "macros" . DIRECTORY_SEPARATOR;
    }

    /**
     * Get pages path
     *
     * @param string $template
     * @param string $type
     * @return string
     */
    public static function getPagesPath($template = null, $type = null)
    {
        return Self::getTemplatePath($template,$type) . "pages" . DIRECTORY_SEPARATOR; 
    }

    /**
     * Get template path
     *
     * @param string $template
     * @param string $type
     * @return string
     */
    public static function getTemplatePath($template = null, $type = null) 
    {   
        switch($type) {
            case Component::EXTENSION_COMPONENT:
                return Self::getExtensionViewPath($template);
            case Component::TEMPLATE_COMPONENT:
                return  Self::TEMPLATES_PATH . $template . DIRECTORY_SEPARATOR;
            case Component::GLOBAL_COMPONENT:
                return Path::ARIKAIM_VIEW_PATH . DIRECTORY_SEPARATOR;
        }                     
    }

    /**
     * Get library file path
     *
     * @param string $library
     * @param string $fileName
     * @return string
     */
    public static function getLibraryFilePath($library, $fileName) {
        return Self::getLibraryPath($library) . DIRECTORY_SEPARATOR . $fileName;
    }
    
    /**
     * Get library path
     *
     * @param string $library
     * @return string
     */
    public static function getLibraryPath($library)
    {
        return Self::LIBRARY_PATH . $library;
    }

    /**
     * Get extension view path
     *
     * @param string $extension
     * @return string
     */
    public static function getExtensionViewPath($extension)
    {
        return Path::EXTENSIONS_PATH . $extension . DIRECTORY_SEPARATOR . "view" . DIRECTORY_SEPARATOR;
    }

    /**
     * Get extension jobs path
     *
     * @param string $extension
     * @return string
     */
    public static function getExtensionJobsPath($extension)   
    {
        return Path::EXTENSIONS_PATH . $extension . DIRECTORY_SEPARATOR . 'jobs' . DIRECTORY_SEPARATOR;
    }

    /**
     * Get extension subscribers path.
     *
     * @param string $extension
     * @return string
     */
    public static function getExtensionSubscribersPath($extension)   
    {
        return Self::EXTENSIONS_PATH . $extension . DIRECTORY_SEPARATOR . 'subscribers' . DIRECTORY_SEPARATOR;
    }
    
    /**
     * Get extension model path
     *
     * @param string $extension
     * @return string
     */
    public static function getExtensionModelsPath($extension)   
    {
        return Self::EXTENSIONS_PATH . $extension . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR;
    }

    /**
     * Get extensions models schema path
     *
     * @param string $extension
     * @return string
     */
    public static function getExtensionModelsSchemaPath($extension)   
    {
        return Self::getExtensionModelsPath($extension) . 'schema' . DIRECTORY_SEPARATOR;
    }

    /**
     * Get extension pages path
     *
     * @param string $extension
     * @return string
     */
    public static function getExtensionPagesPath($extension)  
    {
        return Self::getExtensionViewPath($extension) . 'pages' . DIRECTORY_SEPARATOR;
    }

    /**
     * Get extension macros path
     *
     * @param string $extension
     * @return string
     */
    public static function getExtensionMacrosPath($extension)
    {
        return Path::getExtensionViewPath($extension) . "macros" . DIRECTORY_SEPARATOR;
    }

    /**
     * Get extension macros relative path
     *
     * @param string $extension
     * @return string
     */
    public static function getExtensionMacrosRelativePath($extension)
    {
        return $extension . DIRECTORY_SEPARATOR . "view" . DIRECTORY_SEPARATOR . "macros" . DIRECTORY_SEPARATOR;
    }

    /**
     * Get extension components path
     *
     * @param string $extension
     * @return string
     */
    public static function getExtensionComponentsPath($extension)  
    {
        return Path::getExtensionViewPath($extension) . 'components' . DIRECTORY_SEPARATOR;
    }

    /**
     * Get extension component path
     *
     * @param string $extension
     * @param string $componentName
     * @return string
     */
    public static function getExtensionComponentPath($extension, $componentName)  
    {
        return Self::getExtensionComponentsPath($extension) . $componentName;
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
        $parentPath = dirname($path);

        return ($parentPath == "." || empty($path) == true) ? false : $parentPath;          
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
