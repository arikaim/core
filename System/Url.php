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

use Arikaim\Core\View\Theme;
use Arikaim\Core\Arikaim;

/**
 * Url helper
 */
class Url
{   
    const ARIKAIM_BASE_URL = ARIKAIM_DOMAIN . ARIKAIM_BASE_PATH;
    const ARIKAIM_URL      = Self::ARIKAIM_BASE_URL . '/arikaim';
    const ARIKAIM_VIEW_URL = Self::ARIKAIM_URL . '/view';
    const LIBRARY_URL      = Self::ARIKAIM_VIEW_URL . '/library';
    const EXTENSIONS_URL   = Self::ARIKAIM_URL . '/extensions';
    const TEMPLATES_URL    = Self::ARIKAIM_VIEW_URL . '/templates';
    const COMPONENTS_URL   = Self::ARIKAIM_VIEW_URL . '/components';
  
    /**
     * Get theme file url
     *
     * @param string $template
     * @param string $theme
     * @param string $themeFile
     * @return string
     */
    public static function getThemeFileUrl($template, $theme, $themeFile)
    {
        return (empty($themeFile) == true) ? null : Self::getTemplateThemeUrl($template,$theme) . $themeFile;       
    }

    /**
     * Get template theme url
     *
     * @param string $template
     * @param string $theme
     * @return string
     */
    public static function getTemplateThemeUrl($template, $theme)
    {
        return Self::getTemplateThemesUrl($template) . "/$theme/";
    }

    /**
     * Get template url
     *
     * @param string $template
     * @return string
     */
    public static function getTemplateUrl($template) 
    {       
        return Self::TEMPLATES_URL . "/$template";       
    }

    /**
     * Get template themes url
     *
     * @param string $template
     * @return string
     */
    public static function getTemplateThemesUrl($template)
    {
        return Self::getTemplateUrl($template) . "/themes";
    }
    
    /**
     * Get UI library themes url
     *
     * @param string $library
     * @return string
     */
    public static function getLibraryThemesUrl($library)
    {
        return Self::getLibraryUrl($library) . "/themes";
    }

    /**
     * Get UI library theme url
     *
     * @param string $library
     * @param string $theme
     * @return string
     */
    public static function getLibraryThemeUrl($library, $theme = Theme::DEFAULT_THEME_NAME)
    {
        return Self::getLibraryUrl($library) . "/themes/$theme/";
    }

    /**
     * Get UI library theme file url
     *
     * @param string $library
     * @param string $file
     * @param string $theme
     * @return string
     */
    public static function getLibraryThemeFileUrl($library, $file, $theme = Theme::DEFAULT_THEME_NAME)
    {
        return Self::getLibraryThemeUrl($library,$theme) . $file;
    }

    /**
     * Get UI library url
     *
     * @param string $library
     * @return string
     */
    public static function getLibraryUrl($library)
    {
        return Self::LIBRARY_URL . "/$library";
    }

    /**
     * Get UI library file url
     *
     * @param string $library
     * @param string $fileName
     * @return string
     */
    public static function getLibraryFileUrl($library, $fileName)
    {
        return Self::getLibraryUrl($library) . "/$fileName";
    }

    /**
     * Get extension view url
     *
     * @param string $extension
     * @return string
     */
    public static function getExtensionViewUrl($extension)
    {
        return Self::EXTENSIONS_URL . "/$extension/view";
    }


     /**
     * Fetch url
     *
     * @param string $url
     * @param array $options
     * @return Response|null
     */
    public static function fetch($url, $options = [])
    {
        if (Self::isValid($url) == false) {
            return null;
        }
        $response = Arikaim::http()->get($url,$options);

        return (is_object($response) == true) ? $response->getBody() : null;
    }

    /**
     * Verify url
     *
     * @param string $url
     * @return boolean
     */
    public static function verify($url)
    {
        if (Self::isValid($url) == false) {
            return false;
        }
        $response = Arikaim::http()->get($url);
        $status = (is_object($response) == true) ? $response->getStatusCode() : null;

        return !(empty($status) == true || $status == 404);
    }

    /**
     * Return true if url is valid
     *
     * @param string $url
     * @return boolean
     */
    public static function isValid($url)
    {
        return (filter_var($url,FILTER_VALIDATE_URL) == true) ? true : false; 
    }
}
