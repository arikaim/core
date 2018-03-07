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

use Arikaim\Core\Arikaim;
use Arikaim\Core\Form\Properties;
use Arikaim\Core\View\Template;
use Arikaim\Core\View\UiLibrary;

class Theme 
{
    const DEFAULT_THEME_NAME = 'default';

    public function __construct() 
    {
    }

    public static function getLibraryThemeUrl($library_name, $theme_name = Self::DEFAULT_THEME_NAME)
    {
        return UiLibrary::getLibraryUrl($library_name) .  "/themes/$theme_name/";
    }

    public static function getLibraryThemesUrl($library_name)
    {
        return UiLibrary::getLibraryUrl($library_name) .  "/themes";
    }

    public static function getLibraryThemesPath($library_name)
    {
        return UiLibrary::getLibraryPath($library_name) . DIRECTORY_SEPARATOR . "themes";
    }

    public static function getLibraryThemePath($library_name, $theme_name = Self::DEFAULT_THEME_NAME)
    {
        return Self::getLibraryThemesPath($library_name) . DIRECTORY_SEPARATOR . $theme_name;
    }

    public static function getTemplateThemesUrl($template_name)
    {
        return Template::getTemplateUrl($template_name) . "/themes";
    }

    public static function getTemplateThemeUrl($template_name, $theme_name = Self::DEFAULT_THEME_NAME)
    {
        return Template::getTemplateUrl($template_name) . "/themes/$theme_name/";
    }

    public static function getTemplateThemesPath($template_name)
    {
        return Template::getTemplatePath($template_name) . DIRECTORY_SEPARATOR . "themes";
    }

    public static function getTemplateThemePath($template_name, $theme_name = Self::DEFAULT_THEME_NAME)
    {
        return Self::getTemplateThemesPath($template_name) . DIRECTORY_SEPARATOR . $theme_name;
    }

    public static function getCurrentTheme($template_name = null, $default_theme = null)
    {
        if (empty($default_theme) == true) {
            $default_theme = Self::DEFAULT_THEME_NAME;
        }
       
        try {            
            if (is_object(Arikaim::options()) == false) {
                return $default_theme;
            } 
        } catch(\Exception $e) {
            return $default_theme;
        }

        if ($template_name == null) {
            $template_name = Template::getTemplateName();
        }
        return Arikaim::options()->get("current.theme.$template_name",$default_theme);
    }

    public static function setCurrentTheme($theme_name, $template_name = null)
    {
        if (empty($template_name) == true) {
            $template_name = Template::getTemplateName();
        }
        return Arikaim::options()->set("current.theme.$template_name",$theme_name);     
    }

    public static function getThemeFile($properties, $theme_name)
    {
        return $properties->getByPath("themes/$theme_name/file","");
    }   
}
