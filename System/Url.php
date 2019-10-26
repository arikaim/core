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
use Arikaim\Core\Utils\Utils;

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
     * @param string $template_name
     * @param string $theme_name
     * @param string $theme_file
     * @return string
     */
    public static function getThemeFileUrl($template_name, $theme_name, $theme_file)
    {
        return (empty($theme_file) == true) ? null : Self::getTemplateThemeUrl($template_name,$theme_name) . $theme_file;       
    }

    /**
     * Get template theme url
     *
     * @param string $template_name
     * @param string $theme_name
     * @return string
     */
    public static function getTemplateThemeUrl($template_name, $theme_name)
    {
        return Self::getTemplateThemesUrl($template_name) . "/$theme_name/";
    }

    /**
     * Get template url
     *
     * @param string $template_name
     * @return string
     */
    public static function getTemplateUrl($template_name) 
    {       
        return Self::TEMPLATES_URL . "/$template_name";       
    }

    /**
     * Get template themes url
     *
     * @param string $template_name
     * @return string
     */
    public static function getTemplateThemesUrl($template_name)
    {
        return Self::getTemplateUrl($template_name) . "/themes";
    }
    
    /**
     * Get UI library themes url
     *
     * @param string $library_name
     * @return string
     */
    public static function getLibraryThemesUrl($library_name)
    {
        return Self::getLibraryUrl($library_name) . "/themes";
    }

    /**
     * Get UI library theme url
     *
     * @param string $library_name
     * @param string $theme_name
     * @return string
     */
    public static function getLibraryThemeUrl($library_name, $theme_name = Theme::DEFAULT_THEME_NAME)
    {
        return Self::getLibraryUrl($library_name) . "/themes/$theme_name/";
    }

    /**
     * Get UI library theme file url
     *
     * @param string $library_name
     * @param string $file
     * @param string $theme_name
     * @return string
     */
    public static function getLibraryThemeFileUrl($library_name, $file, $theme_name = Theme::DEFAULT_THEME_NAME)
    {
        return Self::getLibraryThemeUrl($library_name,$theme_name) . $file;
    }

    /**
     * Get UI library url
     *
     * @param string $library_name
     * @return string
     */
    public static function getLibraryUrl($library_name)
    {
        return Self::LIBRARY_URL . "/$library_name";
    }

    /**
     * Get UI library file url
     *
     * @param string $library_name
     * @param string $file_name
     * @return string
     */
    public static function getLibraryFileUrl($library_name, $file_name)
    {
        return Self::getLibraryUrl($library_name) . "/$file_name";
    }

    /**
     * Get extension view url
     *
     * @param string $extension_name
     * @return string
     */
    public static function getExtensionViewUrl($extension_name)
    {
        return Self::EXTENSIONS_URL . "/$extension_name/view";
    }


     /**
     * Fetch url
     *
     * @param string $url
     * @return Response|null
     */
    public static function fetch($url)
    {
        if (Self::isValid($url) == false) {
            return null;
        }
        $response = Arikaim::http()->get($url);
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
        return (filter_var($url, FILTER_VALIDATE_URL) == true) ? true : false; 
    }
}
