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
use Arikaim\Core\View\Template;

class Theme 
{
    const DEFAULT_THEME_NAME = 'default';

    public function __construct() 
    {
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
