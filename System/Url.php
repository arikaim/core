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

class Url
{
    const ARIKAIM_BASE_URL = ARIKAIM_DOMAIN . ARIKAIM_BASE_PATH;
    const ARIKAIM_URL      = Self::ARIKAIM_BASE_URL . '/arikaim';
    const ARIKAIM_VIEW_URL = Self::ARIKAIM_URL . '/view';
    const LIBRARY_URL      = Self::ARIKAIM_VIEW_URL . '/library';
    const EXTENSIONS_URL   = Self::ARIKAIM_URL . '/extensions';
    const TEMPLATES_URL    = Self::ARIKAIM_VIEW_URL . '/templates';

    const REPOSITORY_URL   = 'https://www.arikaim.com/store/';
    
    public static function getThemeFileUrl($template_name, $theme_name, $theme_file)
    {
        return Self::getTemplateThemeUrl($template_name,$theme_name) . $theme_file;       
    }

    public static function getTemplateThemeUrl($template_name, $theme_name)
    {
        return Self::getTemplateThemesUrl($template_name) . "/$theme_name/";
    }

    public static function getTemplateUrl($template_name) 
    {       
        return Self::TEMPLATES_URL . "/$template_name";       
    }

    public static function getTemplateThemesUrl($template_name)
    {
        return Self::getTemplateUrl($template_name) . "/themes";
    }
    
    public static function getLibraryThemesUrl($library_name)
    {
        return Self::getLibraryUrl($library_name) . "/themes";
    }

    public static function getLibraryThemeUrl($library_name, $theme_name = 'default')
    {
        return Self::getLibraryUrl($library_name) . "/themes/$theme_name/";
    }

    public static function getLibraryThemeFileUrl($library_name, $file, $theme_name = 'default')
    {
        return Self::getLibraryThemeUrl($library_name,$theme_name) . $file;
    }

    public static function getLibraryUrl($library_name)
    {
        return Self::LIBRARY_URL . "/$library_name";
    }

    public static function getLibraryFileUrl($library_name, $file_name)
    {
        return Self::getLibraryUrl($library_name) . "/$file_name";
    }

    public static function getExtensionViewUrl($extension_name)
    {
        return Self::EXTENSIONS_URL . "/$extension_name/view";
    }
}