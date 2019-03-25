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
    const ARIKAIM_VIEW_URL = Self::ARIKAIM_BASE_URL . '/arikaim/view';
    const LIBRARY_ROOT_URL = Self::ARIKAIM_VIEW_URL . '/library';
    const REPOSITORY_URL   = 'https://www.arikaim.com/store/';
    
    public static function getTemplateThemesUrl($template_name)
    {
        return Template::getTemplateUrl($template_name) . "/themes";
    }
    
    public static function getLibraryThemesUrl($library_name)
    {
        return Self::getLibraryUrl($library_name) .  "/themes";
    }

    public static function getLibraryThemeUrl($library_name, $theme_name = 'default')
    {
        return Self::getLibraryUrl($library_name) .  "/themes/$theme_name/";
    }

    public static function getLibraryThemeFileUrl($library_name, $file, $theme_name = 'default')
    {
        return Self::getLibraryThemeUrl($library_name,$theme_name) . $file;
    }

    public static function getLibraryUrl($library_name)
    {
        return Self::LIBRARY_ROOT_URL . "/$library_name";
    }

    public static function getLibraryFileUrl($library_name, $file_name)
    {
        return Self::getLibraryUrl($library_name) . "/$file_name";
    }

    public static function getExtensionViewUrl($extension_name)
    {
        return join('/',array(Self::ARIKAIM_BASE_URL,'arikaim','extensions',$extension_name,'view'));
    }
}